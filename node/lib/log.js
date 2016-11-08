var Config     = require('./config');
var db         = require('./db');
var JSONPath   = require('advanced-json-path');
var Properties = require('./properties');
var Actions    = require('./actions');

// Log
var Log = module.exports = {
	record: function (data, dbConfig, callback) {
		var ref = JSONPath(data, '$.EventData.ref');
		if (ref === false) {
			ref = JSONPath(data, '$.Command.ref');
		}
		
		if (
			typeof dbConfig == 'undefined' || 
			dbConfig == null ||
			ref === 'get-state'
		) {
			callback(null);
			return;
		}
		
		var dbLogger = db.connect('Logger', dbConfig);
		var nodeID = Properties.value('NodeID', data);
		if (nodeID == false) {
			nodeID = null;
		}
		
		dbLogger.query(
			{
				text: 'INSERT INTO "Events" ("nodeID", "data") VALUES ($1, $2) RETURNING id',
				values: [nodeID, data]
			},
			
			function (result) {
				var logID = null;
				if (result instanceof Error) {
					console.log('LOG: '+result);
				} else {
					logID = result.rows[0].id;
				}
				
				if (typeof callback == 'function') {
					callback(
						ref ? ref : logID
					);
				}
			}
		);
	},
	
	clean: function (config) {
		var days = Config.system('log.days');
		if (days == 0) {
			return;
		}
		
		console.log('LOG: Clean events older than %d days', days);
		var dbLogger = db.connect('Logger', Config.system(config));
		dbLogger.query(
			{
				text: 'DELETE FROM "Events" WHERE "timestamp" < CURRENT_TIMESTAMP - CAST($1 AS INTERVAL)',
				values: [days+' day']
			},
			
			function (result) {
				if (result instanceof Error) {
					console.log('LOG Clean: '+result);
				}
			}
		);
	}
}

Actions.
	register(
		'Log.clean', 
		function (params) {
			Log.clean(params.config);
		},
		null,
		true
	).
	parameter('config', 'string', true)
;
