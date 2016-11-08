var JSONPath    = require('advanced-json-path');
var Config      = require('../lib/config');
var REST        = require('../lib/rest');
var Actions     = require('../lib/actions');
var Connections = require('../lib/connections');

var IFTTT = module.exports = {
	
	_event: null,
	_key: null,
	
	sanitize: function (str) {
		if (typeof str != 'string') {
			return str;
		}
		
		return str.
			replace(/[\s\-]+/, '-').
			replace(/[^A-Za-z0-9\-\_]+/, '-')
		;
	},
	
	init: function (params) {
		this._event = this.sanitize(params.event);
		
		this._key = this.sanitize(
			Config.user(params.userID, 'ifttt.key')
		);
		
		if (this._key === false) {
			throw new Error('IFTTT Maker Channel key is not set in config');
		}
	},
	
	event: function (params) {
		
		this.init(params);
		
		REST.request(
			{
				debug: true,
				secure: true,
				host: 'maker.ifttt.com',
				path: '/trigger/'+this._event+'/with/key/'+this._key,
				headers: {
					'Content-Type': 'application/json'
				},
				post: {
					value1: params.value1,
					value2: params.value2,
					value3: params.value3
				}
			},
			
			function (status, response) {
				if (status instanceof Error) {
					throw Error;
					return;
				}
				
				console.log('IFTTT: [%d] %s', status, response);
				if (status != 200) {
					try {
						var data = {};
						try {
							data = JSON.parse(response);
						} catch (err) {
							var title = response.match(/<title>(.*)<\/title>/); 
							throw new Error('IFTTT ['+status+']'+(title ? ' '+title[1] : ''));
						}
						throw new Error('IFTTT ['+status+'] '+Array.prototype.concat(JSONPath(data, '$..message')).join(', '));
					} catch (err) {
						Connections.error(err, params.userID);
					}
				}
			}
		);
	}
	
};

Actions.
	register(
		'IFTTT.Maker.event', 
		function (params) {
			IFTTT.event(params)
		},
		'Triggers IFTTT Maker Channel event. Read more on <a href="https://ifttt.com/maker" target="_blank">IFTTT site</a>.'
	).
	parameter('event',  'string', true).
	parameter('value1', 'string', false, 'propertySelector').
	parameter('value2', 'string', false, 'propertySelector').
	parameter('value3', 'string', false, 'propertySelector')
;
