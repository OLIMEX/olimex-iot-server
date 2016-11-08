var JSONPath    = require('advanced-json-path');
var TriggerType = require('./type');

/****************************************************************************
 * TriggerType - everyHour
 ****************************************************************************/
everyHour.prototype = new TriggerType();
everyHour.prototype.constructor = everyHour;

function everyHour(options) {
	this.minutes = this.valueToNumber(JSONPath(options, '$.minutes'));
	this.jsonPath = '$' + this.devicePath('CLOCK') + this.propertyPath('CLOCK', 'Minutes');
}

everyHour.prototype.toString = function () {
	return 'everyHour ['+this.minutes+']';
};

everyHour.prototype.execute = function (data, changes) {
	var result = JSONPath(changes.after, this.path());
	if (result === false) {
		return false;
	}
	
	var minutes = JSONPath(result, this.getProperty('CLOCK', 'Minutes'));
	
	return (this.minutes === minutes);
};

module.exports = everyHour;
