var Filters = require('../lib/filters');

function HysteresisSwitch(params) {
	console.log('HysteresisSwitch(%j)', params);
	
	this.lowLevel  = Math.min(params.lowLevel, params.highLevel);
	this.highLevel = Math.max(params.lowLevel, params.highLevel);
	
	this.invert = (typeof params.invert == 'undefined' ? 
		false 
		: 
		params.invert
	);
	
	this.onlyON = (typeof params.onlyON == 'undefined' ? 
		false 
		: 
		params.onlyON
	);
}

HysteresisSwitch.prototype.getState = function () {
	var state = this.invert ? 1 ^ this.state : this.state;
	console.log('HysteresisSwitch: State ['+state+']'+(this.invert ? ' inverted' : ''));
	if (this.onlyON) {
		if (state) {
			return state;
		}
		return false;
	}
	return state;
}

HysteresisSwitch.prototype.apply = function (value) {
	if (typeof this.state == 'undefined') {
		this.state = (value > this.highLevel ? 1 : 0);
		return this.getState();
	}
	
	if (value > this.highLevel) {
		if (this.state == 1) {
			return false;
		}
		this.state = 1;
		return this.getState();
	}
	
	if (value < this.lowLevel) {
		if (this.state == 0) {
			return false;
		}
		this.state = 0;
		return this.getState();
	}
	
	return false;
}

Filters.
	register(
		'HysteresisSwitch', 
		HysteresisSwitch,
		'Switch ON (output value 1) when input pass-over highLevel parameter and OFF (output value 0) '+
		'when input pass-below lowLevel. Invert parameter is used to reverse ON and OFF behavior. '+
		'If onlyON parameter is YES then action will be executed only when output value is 1.'
	).
	parameter('lowLevel',  'number',   true).
	parameter('highLevel', 'number',   true).
	parameter('invert',    'boolean',  false, 'booleanNoYes').
	parameter('onlyON',    'boolean',  false, 'booleanNoYes')
;
