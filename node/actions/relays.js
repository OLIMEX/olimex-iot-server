var JSONPath     = require('advanced-json-path');

var Actions      = require('../lib/actions');
var Connections  = require('../lib/connections');
var Properties   = require('../lib/properties');
var State        = require('../lib/state');

var Relays = module.exports = {
	connection: null,
	ref: null,
	url: '/relay',
	property: 'Relay',
	
	init: function (params) {
		this.connection = Connections.findNode(params.userID, params.node);
		if (this.connection === null) {
			throw new Error('RELAYS: Node ['+params.node+'] not connected');
		}
		
		this.ref = typeof params.ref == 'undefined' ? 
			null 
			: 
			params.ref
		;
		
		this.url = '/relay';
		if (JSONPath(this.connection.node.Devices, '$.[?(@.URL == "/switch1")]')) {
			this.url = '/switch1';
		} else if (JSONPath(this.connection.node.Devices, '$.[?(@.URL == "/switch2")]')) {
			this.url = '/switch2';
		}
		
		if (typeof params.relayID == 'undefined') {
			params.relayID = '';
		}
		
		this.property = 'Relay'+params.relayID;
	},
	
	post: function (data) {
		this.connection.sendUTF(
			JSON.stringify(
				{
					Method: 'POST',
					URL:    this.url,
					Data:   JSON.parse(data),
					ref:    this.ref
				}
			)
		);
	},
	
/****************************************************************************
 * Set relay state On
 ****************************************************************************/
	on: function (params) {
		this.init(params);
		this.post(
			'{"'+this.property+'": 1}'
		);
	},
	
/****************************************************************************
 * Set relay state Off
 ****************************************************************************/
	off: function (params) {
		this.init(params);
		this.post(
			'{"'+this.property+'": 0}'
		);
	},
	
/****************************************************************************
 * Toggle relay current state
 ****************************************************************************/
	toggle: function (params) {
		this.init(params);
		this.post(
			'{"'+this.property+'": 2}'
		);
	},

/****************************************************************************
 * Set relay state
 ****************************************************************************/
	set: function (params) {
		this.init(params);
		this.post(
			'{"'+this.property+'": '+(params.state ? 1 : 0)+'}'
		);
	},
	
/****************************************************************************
 * On relay for milliseconds then Off
 ****************************************************************************/
	onOff: function (params) {
		this.init(params);
		this.post(
			'{"'+this.property+'": '+parseInt(params.ms)+'}'
		);
	},

/****************************************************************************
 * Off relay for milliseconds then On
 ****************************************************************************/
	offOn: function (params) {
		this.init(params);
		this.post(
			'{"'+this.property+'": -'+parseInt(params.ms)+'}'
		);
	}
};

Actions.
	register(
		'Relays.on', 
		function (params) {
			Relays.on(params)
		},
		'Set relay ON. Supported boards ESP8266-EVB, ESP8266-EVB-BAT, ESP-PLUG, ESP-SWITCH1, ESP-SWITCH2'
	).
	parameter('node',    'string', true, 'nodeSelector').
	parameter('relayID', 'number', false, 'relaySelector')
;

Actions.
	register(
		'Relays.off', 
		function (params) {
			Relays.off(params)
		},
		'Set relay OFF. Supported boards ESP8266-EVB, ESP8266-EVB-BAT, ESP-PLUG, ESP-SWITCH1, ESP-SWITCH2'
	).
	parameter('node',    'string', true, 'nodeSelector').
	parameter('relayID', 'number', false, 'relaySelector')
;

Actions.
	register(
		'Relays.toggle', 
		function (params) {
			Relays.toggle(params);
		},
		'Toggle relay state. Supported boards ESP8266-EVB, ESP8266-EVB-BAT, ESP-PLUG, ESP-SWITCH1, ESP-SWITCH2'
	).
	parameter('node',    'string', true, 'nodeSelector').
	parameter('relayID', 'number', false, 'relaySelector')
;

Actions.
	register(
		'Relays.set', 
		function (params) {
			Relays.set(params)
		},
		'Set relay state. Supported boards ESP8266-EVB, ESP8266-EVB-BAT, ESP-PLUG, ESP-SWITCH1, ESP-SWITCH2'
	).
	parameter('node',    'string', true, 'nodeSelector').
	parameter('relayID', 'number', false, 'relaySelector').
	parameter('state',   'number', true, 'propertySelector')
;

Actions.
	register(
		'Relays.onOff', 
		function (params) {
			Relays.onOff(params)
		},
		'Turns relay ON for desired milliseconds then turns it OFF. '+
		'Supported boards ESP8266-EVB, ESP8266-EVB-BAT, ESP-PLUG, ESP-SWITCH1, ESP-SWITCH2.'
	).
	parameter('node',    'string', true, 'nodeSelector').
	parameter('relayID', 'number', false, 'relaySelector').
	parameter('ms',      'number', true)
;

Actions.
	register(
		'Relays.offOn', 
		function (params) {
			Relays.onOff(params)
		},
		'Turns relay OFF for desired milliseconds then turns it ON. '+
		'Supported boards ESP8266-EVB, ESP8266-EVB-BAT, ESP-PLUG, ESP-SWITCH1, ESP-SWITCH2.'
	).
	parameter('node',    'string', true, 'nodeSelector').
	parameter('relayID', 'number', false, 'relaySelector').
	parameter('ms',      'number', true)
;
