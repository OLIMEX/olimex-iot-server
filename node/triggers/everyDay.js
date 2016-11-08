var JSONPath    = require('advanced-json-path');
var Clock       = require('../lib/clock');
var TriggerType = require('./type');

/****************************************************************************
 * TriggerType - everyDay
 ****************************************************************************/
everyDay.prototype = new TriggerType();
everyDay.prototype.constructor = everyDay;

function everyDay(options) {
	var minutes = this.valueToNumber(JSONPath(options, '$.minutes'));
	var hour    = this.valueToNumber(JSONPath(options, '$.hour'));
	var dowMask = this.valueToNumber(JSONPath(options, '$.dowMask'));
	
	this.time = Clock.time(hour, minutes);
	this.dow = Clock.dowFromMask(dowMask);
	
	this.jsonPath = '$' + this.devicePath('CLOCK') + this.propertyPath('CLOCK', 'Time');
}

everyDay.prototype.toString = function () {
	return 'everyDay ['+this.dow.join(', ')+' '+this.time+']';
};

everyDay.prototype.execute = function (data, changes) {
	var result = JSONPath(changes.after, this.path());
	if (result === false) {
		return false;
	}
	
	var time = JSONPath(data, this.getProperty('CLOCK', 'Time'));
	var day  = JSONPath(data, this.getProperty('CLOCK', 'DayOfWeek'));
	
	return (
		this.time === time && 
		this.dow.indexOf(day) !== false
	);
};

module.exports = everyDay;
