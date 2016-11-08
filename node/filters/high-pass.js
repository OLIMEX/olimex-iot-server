var Filters = require('../lib/filters');

function HighPass(params) {
	console.log('HighPass(%j)', params);
	
	this.weight = params.weight;
	this.round = typeof params.decimals == 'undefined' ?
		Math.pow(10, 2)
		:
		Math.pow(10, params.decimals)
	;
	
	if (this.weight > 1) {
		this.weight = 1;
	}
	
	if (this.weight < 0) {
		this.weight = 0;
	}
	
	this.weight = 1 - this.weight;
}

HighPass.prototype.apply = function (value) {
	if (typeof this.out == 'undefined') {
		this.out = value;
		this.input = value;
		return this.out;
	}
	
	this.out = this.weight * (this.out + value - this.input);
	this.input = value;
	
	this.out = Math.round(this.out * this.round) / this.round;
	return this.out;
}

Filters.
	register(
		'HighPass', 
		HighPass,
		'Allow only high frequency changes to pass. Weight factor must be between 0 and 1. '+
		'0 means no change of input. 1 means flat 0 output.'
	).
	parameter('weight',   'number',   true).
	parameter('decimals', 'number',   false)
;
