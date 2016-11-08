var TriggerType = require('./type');

/****************************************************************************
 * TriggerType - raw
 ****************************************************************************/
raw.prototype = new TriggerType();
raw.prototype.constructor = raw;

function raw(options) {
	this.jsonPath = options;
}

raw.prototype.toString = function () {
	return 'raw ['+this.jsonPath+']';
}

module.exports = raw;
