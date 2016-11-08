var JSONPath    = require('advanced-json-path');
var Properties  = require('../lib/properties');
var Clock       = require('../lib/clock');
var TriggerType = require('./type');

/****************************************************************************
 * TriggerType - everyMonth
 ****************************************************************************/
everyMonth.prototype = new TriggerType();
everyMonth.prototype.constructor = everyMonth;

function everyMonth(options) {
	var minutes = this.valueToNumber(JSONPath(options, '$.minutes'));
	var hour    = this.valueToNumber(JSONPath(options, '$.hour'));
	
	this.time = Clock.time(hour, minutes);
	this.date = this.valueToNumber(JSONPath(options, '$.date'));
	
	this.jsonPath = '$' + this.devicePath('CLOCK') + this.propertyPath('CLOCK', 'Time');
}

everyMonth.prototype.toString = function () {
	return 'everyMonth ['+this.date+' '+this.time+']';
};

everyMonth.prototype.execute = function (data, changes) {
	var result = JSONPath(changes.after, this.path());
	if (result === false) {
		return false;
	}
	
	var time = JSONPath(data, this.getProperty('CLOCK', 'Time'));
	var date = JSONPath(data, this.getProperty('CLOCK', 'Date'));
	
	var realDate = this.date;
	if (realDate == 0) {
		var now = new Date();
		var lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
		realDate = lastDay.getDate();
	}
	
	return (
		this.time === time && 
		realDate === date
	);
};

module.exports = everyMonth;
