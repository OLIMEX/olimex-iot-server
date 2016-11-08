var JSONPath    = require('advanced-json-path');
var Properties  = require('../lib/properties');
var TriggerType = require('./type');

/****************************************************************************
 * TriggerType - onConnectionClose
 ****************************************************************************/
onConnectionClose.prototype = new TriggerType();
onConnectionClose.prototype.constructor = onConnectionClose;

function onConnectionClose(options) {
	this.node = JSONPath(options, '$.node'); 
	this.jsonPath = '$' + this.nodePath(this.node) + '[?({'+Properties.get(null, 'Status')+'} == "Connection close")]';
}

onConnectionClose.prototype.toString = function () {
	return 'onConnectionClose ['+this.node+']';
}

module.exports = onConnectionClose;
