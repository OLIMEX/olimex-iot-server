var JSONPath    = require('advanced-json-path');
var TriggerType = require('./type');

/****************************************************************************
 * TriggerType - everyMinute
 ****************************************************************************/
everyMinute.prototype = new TriggerType();
everyMinute.prototype.constructor = everyMinute;

function everyMinute(options) {
	this.jsonPath = '$' + this.devicePath('CLOCK') + this.propertyPath('CLOCK', 'Minutes');
}

everyMinute.prototype.toString = function () {
	return 'everyMinute';
};

everyMinute.prototype.execute = function (data, changes) {
	return (JSONPath(changes.after, this.path()) !== false);
};

module.exports = everyMinute;
