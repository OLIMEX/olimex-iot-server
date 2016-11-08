var JSONPath = require('advanced-json-path');

/****************************************************************************
 * Properties
 ****************************************************************************/
var Properties = module.exports = {
	_poll: {},
	
	register: function (options) {
		var deviceID = JSONPath(options, '$.deviceID');
		var device   = JSONPath(options, '$.deviceName');
		
		var id       = JSONPath(options, '$.id');
		var property = JSONPath(options, '$.name');
		var type     = JSONPath(options, '$.type');
		var factor   = JSONPath(options, '$.factor');
		var decimals = JSONPath(options, '$.decimals');
		var path     = JSONPath(options, '$.jsonPath');
		
		if (typeof device != 'string' || deviceID === null) {
			device = '*';
			deviceID = null;
		}
		
		if (typeof this._poll[device] == 'undefined') {
			this._poll[device] = {
				id: [deviceID],
				properties: {}
			};
		} else {
			if (this._poll[device].id.indexOf(deviceID) < 0) {
				this._poll[device].id.push(deviceID);
			}
		}
		
		if (typeof this._poll[device].properties[property] != 'undefined') {
			throw new Error('PROPERTIES: Property ['+device+']['+property+'] is already registerd');
		}
		
		this._poll[device].properties[property] = {
			id:       id,
			type:     type,
			factor:   factor,
			decimals: decimals,
			path:     path
		};
		console.log('PROPERTIES: [%d] %s.%s', id, device, property);
		
		return this;
	},
	
	findDevice: function (deviceID) {
		for (var i in this._poll) {
			if (this._poll[i].id.indexOf(deviceID) >= 0) {
				return i;
			}
		}
		throw new Error('PROPERTIES: No registered properties for deviceID ['+deviceID+']');
	},
	
	changeDevice: function (operation, options) {
		var id     = JSONPath(options, '$.id');
		var device = JSONPath(options, '$.native') ?
			'ESP8266'
			:
			JSONPath(options, '$.name')
		;
		
		if (operation == 'INSERT') {
			this._poll[device] = {
				id: id,
				properties: {}
			};
			console.log('PROPERTIES: %s.*', device);
			return;
		}
		
		var oldDevice = this.findDevice(id);
		if (operation != 'DELETE') {
			if (device != oldDevice) {
				this._poll[device] = this._poll[oldDevice];
				delete this._poll[oldDevice];
			}
			console.log('PROPERTIES: %s.*', device);
		} else {
			console.log('PROPERTIES: %s.* - REMOVED', device);
			delete this._poll[oldDevice];
		}
	},
	
	change: function (operation, options) {
		var id       = JSONPath(options, '$.id');
		var property = JSONPath(options, '$.name');
		var type     = JSONPath(options, '$.type');
		var factor   = JSONPath(options, '$.factor');
		var decimals = JSONPath(options, '$.decimals');
		var path     = JSONPath(options, '$.jsonPath');
		var deviceID = JSONPath(options, '$.deviceID');
		
		var device = this.findDevice(deviceID);
		
		if (operation == 'INSERT') {
			options.deviceName = device;
			this.register(options);
			return;
		}
		
		for (var i in this._poll) {
			for (var j in this._poll[i].properties) {
				if (this._poll[i].properties[j].id === id) {
					delete this._poll[i].properties[j];
					if (operation != 'DELETE') {
						this._poll[device].properties[property] = {
							id:       id,
							type:     type,
							factor:   factor,
							decimals: decimals,
							path:     path
						};
						console.log('PROPERTIES: [%d] %s.%s', id, device, property);
					} else {
						console.log('PROPERTIES: [%d] %s.%s - REMOVED', id, i, j);
					}
					return;
				}
			}
		}
	},
	
	all: function () {
		var data = [];
		for (var i in this._poll) {
			var device = {
				Device: {
					Name: i,
					ID:   this._poll[i].id
				},
				Properties: []
			};
			
			for (var j in this._poll[i].properties) {
				device.Properties.push(
					{
						Name:     j,
						ID:       this._poll[i].properties[j].id,
						Type:     this._poll[i].properties[j].type,
						Factor:   this._poll[i].properties[j].factor,
						Decimals: this._poll[i].properties[j].decimals,
						JSONPath: this._poll[i].properties[j].path
					}
				);
			}
			data.push(device);
		}
		return data;
	},
	
	find: function (device, property) {
		if (typeof device != 'string') {
			device = '*';
		}
		
		var p = JSONPath(this._poll, '$["'+device+'"].properties["'+property+'"]');
		if (p !== false) {
			return p;
		}
		
		p = JSONPath(this._poll, '$["*"].properties["'+property+'"]')
		if (p !== false) {
			return p;
		}
		
		return false;
	},
	
	get: function (device, property) {
		var p = this.find(device, property);
		if (p !== false) {
			return p.path;
		}
		
		return false;
	},
	
	value: function (property, data, unknown) {
		if (typeof unknown == 'undefined') {
			unknown = false;
		}
		
		var device = JSONPath(
			data, 
			this.get(null, 'Device')
		);
		
		var p = this.find(device, property);
		if (p === false) {
			return unknown;
		}
		
		var value = JSONPath(data, p.path);
		if (value === false) {
			return unknown;
		}
		
		if (p.type == 'string') {
			if (typeof value != 'string') {
				value = JSON.stringify(value);
			}
		} else {
			var factor = 1;
			if (p.factor) {
				factor = Number(p.factor);
				if (!factor) {
					factor = 1;
				}
			}
			value = Number(value) / factor;
		}
		return value;
	}
};

