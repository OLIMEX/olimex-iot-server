var JSONPath    = require('advanced-json-path');
var Properties  = require('../lib/properties');
var Filters     = require('../lib/filters');
var TriggerType = require('./type');

/****************************************************************************
 * TriggerType - onPropertyChange
 ****************************************************************************/
onPropertyChange.prototype = new TriggerType();
onPropertyChange.prototype.constructor = onPropertyChange;

function onPropertyChange(options) {
	this.node       = JSONPath(options, '$.node'); 
	this.device     = JSONPath(options, '$.device'); 
	this.property   = JSONPath(options, '$.property'); 
	this.operator   = JSONPath(options, '$.operator');
	this.value      = JSONPath(options, '$.value');
	this.filter     = JSONPath(options, '$.filter');
	
	this.propertyID = null;
	this.dataType   = null;
	
	this.jsonPath = '$' + this.nodePath(this.node) + this.devicePath(this.device) + this.propertyPath(this.device, this.property);
	
	if (typeof this.operator != 'string') {
		this.operator = ' === ';
	}
	
	var property = Properties.find(this.device, this.property);
	if (typeof property !== 'object') {
		return;
	}
	
	this.propertyID = property.id;
	this.dataType   = property.type;
	
	if (this.value) {
		if (this.dataType == 'string') {
			this.value = this.valueToString(this.value);
		} else {
			this.value = this.valueToNumber(this.value);
		}
	}
}

onPropertyChange.prototype.toString = function () {
	return 'onPropertyChange ['+this.node+'.'+this.device+'.'+this.property+']';
}

onPropertyChange.prototype.execute = function (data, changes) {
	var result = JSONPath(changes.after, this.path());
	if (result === false) {
		return false;
	}
	
	if (this.propertyID === null) {
		return false;
	}
	
	var propertyValue = this.propertyValue(this.property, result);
	
	if (this.filter) {
		var filterValue = Filters.apply(
			Properties.value('NodeID', data)+'-'+this.propertyID+'-'+this.trigger.id,
			this.filter,
			this.trigger.prmFilter.execute(false, data),
			propertyValue
		);
		
		if (filterValue === false) {
			return false;
		}
		
		data.Filter = {
			name: this.filter,
			value: filterValue
		};
	}
	
	if (this.value) {
		var condition = JSON.stringify(propertyValue) + this.operator + JSON.stringify(this.value);
		
		// console.log('onPropertyChange: '+condition);
		return eval(condition);
	}
	
	return true;
};

module.exports = onPropertyChange;
