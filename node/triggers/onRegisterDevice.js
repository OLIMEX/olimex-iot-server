var JSONPath    = require('advanced-json-path');
var TriggerType = require('./type');

/****************************************************************************
 * TriggerType - onRegisterDevice
 ****************************************************************************/
onRegisterDevice.prototype = new TriggerType();
onRegisterDevice.prototype.constructor = onRegisterDevice;

function onRegisterDevice(options) {
	this.node       = JSONPath(options, '$.node'); 
	this.deviceURL  = JSONPath(options, '$.deviceURL');
	
	var regDevicePath = this.deviceURL ? 
		'.EventData.Data.Devices[?(@.URL == "'+this.deviceURL+'" && @.Found == 1)]'
		:
		''
	;
	
	this.jsonPath = '$[?($.EventURL == "/devices")]' + this.nodePath(this.node) + regDevicePath;
}

onRegisterDevice.prototype.toString = function () {
	return 'onRegisterDevice ['+this.node+'.'+this.deviceURL+']';
}

onRegisterDevice.prototype.execute = function (data, changes) {
	var result = JSONPath(data, this.path());
	if (result === false) {
		return false;
	}
	
	data.Registered = result;
	return true;
};

module.exports = onRegisterDevice;
