require('./lib/console');

var Config       = require('./lib/config').init(require('../config.json'));

var db           = require('./lib/db');
var Properties   = require('./lib/properties');
var Actions      = require('./lib/actions');
var Triggers     = require('./lib/triggers');
var ParamBuilder = require('./lib/param-builder');
var Connections  = require('./lib/connections');
var Clock        = require('./lib/clock');
var Loader       = require('./lib/loader');

var JSONPath     = require('advanced-json-path');

var dbIoT = db.connect('IoT', Config.system('pg'));

/****************************************************************************
 * Listen for database changes
 ****************************************************************************/
dbIoT.listen(
	'changes', 
	function (msg) {
		try {
			var payload = JSON.parse(msg.payload);
			console.log('CHANGE: %j', payload);
			
			switch (payload.table) {
				case 'Config':
					Config.change(payload.operation, payload.data);
				break;
				
				case 'Users':
					// FIXME - remove all active user connections
				break;
				
				case 'Nodes':
					Connections.changeNode(payload.operation, payload.data);
				break;
				
				case 'Devices':
					Properties.changeDevice(payload.operation, payload.data);
				break;
				
				case 'Properties':
					Properties.change(payload.operation, payload.data);
				break;
				
				case 'Triggers':
					Triggers.change(payload.operation, payload.data);
				break;
				
				case 'Parameters':
					Triggers.changeParams(payload.operation, payload.data);
				break;
			}
		} catch (err) {
			console.log('CHANGE: '+err);
		}
	}
);

/****************************************************************************
 * Register properties
 ****************************************************************************/
dbIoT.query(
	'SELECT '+
		'COALESCE("Native"."name", "Devices"."name") as "deviceName", '+
		'"Properties".* '+
	'FROM '+
		'"Properties" '+
		'LEFT OUTER JOIN "Devices" ON "Devices"."id" = "Properties"."deviceID" '+
		'LEFT OUTER JOIN "Devices" AS "Native" ON "Native"."id" = 1 AND "Devices"."native"'
	,
	function (result) {
		if (result instanceof Error) {
			throw result;
		}
		
		for (var i in result.rows) {
			var property = result.rows[i];
			Properties.register(property);
		};
		
		Loader(__dirname+'/actions/');
		Loader(__dirname+'/filters/');
		registerTriggers();
		
		Clock.run();
	}
);

/****************************************************************************
 * Register triggers
 ****************************************************************************/

function registerTriggers() {
	// System Triggers
	Triggers.register(
		{
			active: true,
			type: 'everyHour',
			data: {
				minutes: 15
			},
			action: 'Log.clean'
		},
		ParamBuilder().
			parameter({name: 'config', value: 'pg'})
	);
	
	// Load Database Triggers
	dbIoT.query(
		'SELECT * FROM "Triggers"',
		function (result) {
			if (result instanceof Error) {
				throw result;
				return;
			}
			
			result.rows.forEach(
				function (trigger) {
					var prmAction = ParamBuilder();
					var prmFilter = ParamBuilder();
					
					Triggers.register(
						trigger,
						prmAction,
						prmFilter
					);
					
					dbIoT.query(
						{
							text: 'SELECT * FROM "Parameters" WHERE "triggerID"=$1 ORDER BY id',
							values: [trigger.id]
						},
						function (result) {
							if (result instanceof Error) {
								throw result;
								return;
							}
							
							result.rows.forEach(
								function (parameter) {
									if (parameter.type == 'action') {
										prmAction.parameter(parameter);
									}
									if (parameter.type == 'filter') {
										prmFilter.parameter(parameter);
									}
								}
							);
						}
					);
				}
			);
		}
	);
}

