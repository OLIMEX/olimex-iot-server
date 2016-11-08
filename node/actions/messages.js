var Connections   = require('../lib/connections');
var Triggers      = require('../lib/triggers');
var Actions       = require('../lib/actions');
var ParamBuilder  = require('../lib/param-builder');
var MessagesQueue = require('./messages-queue');

/****************************************************************************
 * Messages
 ****************************************************************************/
var Messages = module.exports = {
	_queues: [],
	
	init: function (userID, node, device, url, rows, cols, speed, r, g, b) {
		this.remove(userID, node, url);
		
		var queue = new MessagesQueue(userID, node, url, rows, cols, speed, r, g, b);
		
		queue.
			addTrigger(
				Triggers.register(
					{
						active: true,
						type: 'onPropertyChange',
						data: {
							node:     node, 
							device:   null, 
							property: 'Error', 
							operator: null, 
							value:    'Busy'
						},
						action:   'Messages.busy'
					}, 
					ParamBuilder().
						parameter({name: 'node', value: '[NodeToken]'})
				)
			).
			
			addTrigger(
				Triggers.register(
					{
						active: true,
						type: 'onPropertyChange',
						data: {
							node:     node, 
							device:   null, 
							property: 'Status', 
							operator: null, 
							value:    'Done'
						},
						action:   'Messages.done'
					}, 
					ParamBuilder().
						parameter({name: 'node', value: '[NodeToken]'})
				)
			).
			
			addTrigger(
				Triggers.register(
					{
						active: true,
						type: 'onConnectionClose',
						data: {
							node:   node
						}, 
						action: 'Messages.remove'
					},
					ParamBuilder().
						parameter({name: 'node', value: '[NodeToken]'})
				)
			)
		;
		
		this._queues.push(queue);
		return queue;
	},
	
	remove: function (userID, node, url) {
		if (typeof url == 'undefined') {
			url = null;
		}
		
		var connection = Connections.findNode(userID, node);
		var token = connection === null ?
			node
			:
			connection.node.Token
		;
		
		var removed = 0;
		var index = 0;
		while (this._queues.length > index) {
			var current = this._queues[index];
			if (
				current.token() === token && 
				(url === null || current.url() === url)
			) {
				current.removeTriggers();
				this._queues.splice(index, 1);
				removed++;
			} else {
				index++;
			}
		}
		
		return removed;
	},
	
	findQueue: function (params) {
		var connection = Connections.findNode(params.userID, params.node);
		var token = connection === null ?
			params.node
			:
			connection.node.Token
		;
		
		var result = [];
		for (var index in this._queues) {
			var current = this._queues[index];
			if (current.token() === token || token === '*' || token === null) {
				result.push(this._queues[index]);
			}
		}
		
		return result;
	},
	
	register: function (params) {
		this.init(
			params.userID, 
			params.node, 
			params.device, 
			params.url, 
			params.rows, 
			params.cols, 
			params.speed,
			params.r,
			params.g,
			params.b
		);
	},
	
	display: function (params) {
		var queues = this.findQueue(params);
		queues.forEach(
			function (queue) {
				queue.display(params);
			}
		);
	},
	
	setTimeout: function (params) {
		var queues = this.findQueue(params);
		queues.forEach(
			function (queue) {
				queue.setTimeout();
			}
		);
	},
	
	done: function (params) {
		var queues = this.findQueue(params);
		queues.forEach(
			function (queue) {
				queue.done();
			}
		);
	},
	
	busy: function (params) {
		var queues = this.findQueue(params);
		queues.forEach(
			function (queue) {
				queue.busy();
			}
		);
	}
	
};

/****************************************************************************
 * Initialization
 ****************************************************************************/

Actions.
	register(
		'Messages.register', 
		function (params) {
			Messages.register(params);
		},
		'Register message queue and set initial parameters for MOD-LED8x8RGB arrays.'
	).
	parameter('node',   'string', true,  'nodeSelector').
	parameter('device', 'string', true,  'deviceSelector').
	parameter('url',    'string', true,  'propertySelector').
	parameter('r',      'number', false, 'onOffRadio').
	parameter('g',      'number', false, 'onOffRadio').
	parameter('b',      'number', false, 'onOffRadio').
	parameter('rows',   'number', false).
	parameter('cols',   'number', false).
	parameter('speed',  'number', false)
;

Actions.
	register(
		'Messages.display', 
		function (params) {
			Messages.display(params);
		},
		'Display message on registered queue with desired parameters on MOD-LED8x8RGB or ESP-BADGE. '+
		'Message queue have to be initialized onRegisterDevice event with Messages.register action.'
	).
	parameter('node',  'string', true,  'nodeSelector').
	parameter('text',  'string', true,  'propertySelector').
	parameter('r',     'number', false, 'onOffRadio').
	parameter('g',     'number', false, 'onOffRadio').
	parameter('b',     'number', false, 'onOffRadio').
	parameter('rows',  'number', false).
	parameter('cols',  'number', false).
	parameter('speed', 'number', false)
;

Actions.
	register(
		'Messages.setTimeout', 
		function (params) {
			Messages.setTimeout(params);
		},
		null,
		true
	).
	parameter('node', 'string', true)
;

Actions.
	register(
		'Messages.done', 
		function (params) {
			Messages.done(params);
		},
		null,
		true
	).
	parameter('node', 'string', true)
;

Actions.
	register(
		'Messages.busy', 
		function (params) {
			Messages.busy(params);
		},
		null,
		true
	).
	parameter('node', 'string', true)
;

Actions.
	register(
		'Messages.remove', 
		function (params) {
			Messages.remove(params.node);
		},
		null,
		true
	).
	parameter('node', 'string', true)
;
