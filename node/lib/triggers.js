var JSONPath     = require('advanced-json-path');

var Config       = require('./config');
var Log          = require('./log');

var Properties   = require('./properties');
var Actions      = require('./actions');
var Filters      = require('./filters');
var ParamBuilder = require('./param-builder');
var Loader       = require('./loader');

var TriggerTypes = Loader(__dirname+'/../triggers');

/****************************************************************************
 * Triggers
 ****************************************************************************/
var Triggers = module.exports = {
	_poll: [],
	
	register: function (options, prmAction, prmFilter) {
		var id         = JSONPath(options, '$.id'); 
		var userID     = JSONPath(options, '$.userID'); 
		var actionName = JSONPath(options, '$.action');
		var type       = JSONPath(options, '$.type');
		var active     = JSONPath(options, '$.active');
		var data       = JSONPath(options, '$.data');
		
		if (typeof prmAction == 'undefined') {
			prmAction = ParamBuilder();
		}
		
		if (typeof prmFilter == 'undefined') {
			prmFilter = ParamBuilder();
		}
		
		if (typeof type != 'string') {
			throw new Error('TRIGGERS: Invalid trigger type');
		}
		
		if (typeof TriggerTypes[type] != 'function') {
			throw new Error('TRIGGERS: Unknown trigger type ['+type+']');
		}
		type = new TriggerTypes[type](data);
		
		if (typeof actionName != 'string') {
			throw new Error('TRIGGERS: Invalid actionName');
		}
		
		var action = Actions.get(actionName);
		if (action === null) {
			throw new Error('TRIGGERS: Action not found ['+actionName+']');
		}
		
		var trigger = {
			id:         id === false ? '~' : id,
			userID:     userID,
			type:       type,
			active:     active,
			data:       data,
			path:       type.path(),
			actionName: actionName,
			action:     action,
			prmAction:  prmAction,
			prmFilter:  prmFilter
		};
		
		type.trigger = trigger;
		this._poll.push(trigger);
		
		console.log('TRIGGERS: '+trigger.type.toString()+' '+trigger.actionName);
		return trigger;
	},
	
	all: function (user) {
		var data = [];
		
		var userID = JSONPath(user, '$.id');
		if (userID === false) {
			return data;
		}
		
		for (var i in this._poll) {
			if (
				this._poll[i].userID != null &&
				this._poll[i].userID != userID
			) {
				continue;
			}
			
			if (JSONPath(this._poll[i], '$.action.internal')) {
				continue;
			}
			
			data.push(
				{
					ID:         this._poll[i].id,
					UserID:     this._poll[i].userID,
					Active:     this._poll[i].active,
					Type:       this._poll[i].type.name(),
					Data:       this._poll[i].data,
					Action:     this._poll[i].actionName,
					Parameters: this._poll[i].prmAction.parameters(),
					Filter:     this._poll[i].prmFilter.parameters()
				}
			);
		}
		
		return data;
	},
	
	remove: function (trigger) {
		var i = this._poll.indexOf(trigger);
		if (i >= 0) {
			this._poll.splice(i, 1);
		}
	},
	
	find: function (id) {
		for (var i in this._poll) {
			if (this._poll[i].id === id) {
				return this._poll[i];
			}
		}
		return null;
	},
	
	changeParams: function (operation, options) {
		var triggerID = JSONPath(options, '$.triggerID');
		
		var trigger = this.find(triggerID);
		if (trigger === null) {
			return;
		}
		
		trigger.prmAction.change(operation, 'action', options);
		trigger.prmFilter.change(operation, 'filter', options);
		Filters.change(trigger);
	},
	
	change: function (operation, options) {
		var id = JSONPath(options, '$.id');
		
		if (operation == 'INSERT') {
			this.register(
				options,
				ParamBuilder(),
				ParamBuilder()
			);
			return;
		}
		
		var trigger = this.find(id);
		if (trigger === null) {
			return;
		}
		
		Filters.change(trigger);
		
		if (operation != 'DELETE') {
			var prmAction = trigger.prmAction;
			var prmFilter = trigger.prmFilter;
			this.register(
				options,
				prmAction,
				prmFilter
			);
		}
		
		this.remove(trigger);
	},
	
	fire: function (data, changes, refID) {
		var userID = Properties.value('UserID', data);
		
		// Filter triggers 
		var match = JSONPath(
			this._poll, 
			'$.[?('+
				'@.active && @.action '+
				(userID ?
					' && (@.userID == '+userID+' || @.userID == false)'
					:
					''
				) +
			')]'
		);
		
		if (!match) {
			return;
		}
		
		Array.prototype.concat(match).forEach(
			function (current) {
				if (current.type.execute(data, changes)) {
					console.log('TRIGGERS: '+current.type.constructor.name+'['+current.id+'].fire('+current.actionName+')');
					var parameters = current.prmAction ?
						current.prmAction.execute(current.userID, data)
						:
						{
							userID: current.userID
						}
					;
					
					if (refID && current.id !== '~') {
						Log.record(
							{
								Trigger: {
									ref:        refID,
									TriggerID:  current.id,
									Action:     current.actionName,
									Parameters: parameters
								}
							},
							Config.system('pg'),
							function (triggerRefID) {
									parameters.ref = triggerRefID;
									actionExecute(parameters);
							}
						);
						return;
					}
					
					actionExecute(parameters);
					
					function actionExecute(parameters) {
						try {
							current.action.execute(parameters);
						} catch (err) {
							var Connections = require('./connections');
							Connections.error(err, current.userID);
						}
					}
				}
			}
		);
	}
};
