var JSONPath    = require('advanced-json-path');
var Properties  = require('../lib/properties');
var Clock       = require('../lib/clock');
var TriggerType = require('./type');

/****************************************************************************
 * TriggerType - everyYear
 ****************************************************************************/
everyYear.prototype = new TriggerType();
everyYear.prototype.constructor = everyYear;

function everyYear(options) {
	var minutes = this.valueToNumber(JSONPath(options, '$.minutes'));
	var hour    = this.valueToNumber(JSONPath(options, '$.hour'));
	
	this.time  = Clock.time(hour, minutes);
	this.date  = this.valueToNumber(JSONPath(options, '$.date'));
	this.month = JSONPath(options, '$.month');
	
	this.jsonPath = '$' + this.devicePath('CLOCK') + this.propertyPath('CLOCK', 'Time');
}

everyYear.prototype.toString = function () {
	return 'everyYear ['+this.month+' '+this.date+' '+this.time+']';
};

everyYear.prototype.execute = function (data, changes) {
	var result = JSONPath(changes.after, this.path());
	if (result === false) {
		return false;
	}
	
	var time  = JSONPath(data, this.getProperty('CLOCK', 'Time'));
	var date  = JSONPath(data, this.getProperty('CLOCK', 'Date'));
	var month = JSONPath(data, this.getProperty('CLOCK', 'Month'));
	
	return (
		this.time === time && 
		this.date === date &&
		this.month === month
	);
};

module.exports = everyYear;
