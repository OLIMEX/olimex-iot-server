var JSONPath     = require('advanced-json-path');
var Properties   = require('../lib/properties');

/****************************************************************************
 * TriggerType - base object
 ****************************************************************************/

function TriggerType() {
	this.jsonPath = null;
	this.trigger = null;
}

TriggerType.prototype.name = function () {
	return this.constructor.name;
}

TriggerType.prototype.path = function () {
	return this.jsonPath;
}

TriggerType.prototype.toString = function () {
	return '';
}

TriggerType.prototype.valueToString = function (value) {
	if (typeof value != 'string') {
		value = JSON.stringify(value);
	}
	
	return value;
}

TriggerType.prototype.valueToNumber = function (value, valueNaN) {
	if (typeof valueNaN == 'undefined') {
		valueNaN = 0;
	}
	
	if (value === false) {
		value = valueNaN;
	} else {
		value = Number(value);
		if (isNaN(value)) {
			value = valueNaN;
		}
	}
	
	return value;
}

TriggerType.prototype.execute = function (data, changes) {
	return (JSONPath(data, this.path()) !== false);
}

TriggerType.prototype.nodePath = function (node) {
	return (node ?
		'[?({'+Properties.get(null, 'NodeName')+'} == "'+node+'" || {'+Properties.get(null, 'NodeToken')+'} == "'+node+'")]'
		:
		''
	);
};

TriggerType.prototype.devicePath = function (device) {
	return (device ? 
		'[?({'+Properties.get(null, 'Device')+'} == "'+device+'")]'
		:
		''
	);
};

TriggerType.prototype.propertyPath = function (device, property) {
	return (property ?
		'[?({'+Properties.get(device, property)+'} !== false)]'
		:
		''
	);
};

TriggerType.prototype.getProperty = function (device, property) {
	return Properties.get(device, property);
};

TriggerType.prototype.propertyValue = function (property, data) {
	return Properties.value(property, data);
};

module.exports = TriggerType;
