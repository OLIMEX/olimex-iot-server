var Filters = require('../lib/filters');

function LowPass(params) {
	console.log('LowPass(%j)', params);
	
	this.smoothing = params.smoothing;
	this.round = typeof params.decimals == 'undefined' ?
		Math.pow(10, 2)
		:
		Math.pow(10, params.decimals)
	;
	
	if (this.smoothing > 1) {
		this.smoothing = 1;
	}
	
	if (this.smoothing < 0) {
		this.smoothing = 0;
	}
	
	this.smoothing = 1 - this.smoothing;
}

LowPass.prototype.apply = function (value) {
	if (typeof this.out == 'undefined') {
		this.out = value;
		return this.out;
	}
	
	this.out = this.out + this.smoothing * (value - this.out);
	
	this.out = Math.round(this.out * this.round) / this.round;
	return this.out;
}

Filters.
	register(
		'LowPass',
		LowPass,
		'Smooth input signal allowing only low frequency changes to pass. Smoothing factor must be '+
		'between 0 and 1. 0 means no change of input. 1 means flat output.'
	).
	parameter('smoothing',  'number',   true).
	parameter('decimals',   'number',   false)
;
