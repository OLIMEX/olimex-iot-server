var JSONPath = require('advanced-json-path');
var db       = require('./db');

var Config = module.exports = {
	_default: {},
	_system: {},
	_user: {},
	
	init: function (system) {
		this._system = system;
		
		if (this._system.default) {
			this._default = this._system.default;
			delete this._system.default;
		}
		
		var self = this;
		
		console.log('CONFIG: System configuration loaded');
		
		var dbConfig = db.connect('Config', this._system.pg);
		dbConfig.query(
			'SELECT * FROM "Config"',
			function (result) {
				if (result instanceof Error) {
					throw result;
				}
				
				for (var i in result.rows) {
					var config = result.rows[i];
					if (config.userID === null) {
						config.userID = -1;
					}
					self._user[config.userID] = config.data;
				}
				
				console.log('CONFIG: User configuration loaded');
			}
		);
		
		return this;
	},
	
	change: function (operation, data) {
		var userID = JSONPath(data, '$.userID');
		if (!userID) {
			userID = -1;
		}
		
		if (operation == 'DELETE') {
			delete this._user[userID];
			return;
		}
		
		this._user[userID] = JSONPath(data, '$.data');;
	},
	
	set: function (config, name, value) {
		for (var i in name.split('.')) {
			if (typeof config[i] == 'undefined') {
				config[i] = {};
			}
			config = config[i];
		}
		config = value;
	},
	
	system: function (name, value) {
		if (typeof value == 'undefined') {
			value = JSONPath(this._system, '$.'+name);
			if (value) {
				return value;
			}
			
			value = this.user(-1, name);
			if (value) {
				return value;
			}
			
			return JSONPath(this._default, '$.'+name);
		}
		
		this.set(this._system, name, value);
	},
	
	user: function (userID, name, value) {
		if (typeof value == 'undefined') {
			value = JSONPath(this._user, '$.['+userID+'].'+name);
			if (value) {
				return value;
			}
			
			return JSONPath(this._default, '$.'+name);
		}
		
		this.set(this._user, userID+'.'+name, value);
	}
	
};
