var JSONPath   = require('advanced-json-path');
var Properties = require('./properties');

/****************************************************************************
 * ParamBuilder
 ****************************************************************************/
function ParamBuilder() {
	this._parameters = {};
}

ParamBuilder.prototype.parameter = function (options) {
	var id    = JSONPath(options, '$.id');
	var type  = JSONPath(options, '$.type');
	var name  = JSONPath(options, '$.name');
	var value = JSONPath(options, '$.value');
	
	this._parameters[name] = {
		id:    id,
		type:  type,
		value: value
	};
	return this;
};

ParamBuilder.prototype.change = function (operation, type, options) {
	if (type !== JSONPath(options, '$.type')) {
		return;
	}
	
	var id = JSONPath(options, '$.id');
	
	if (operation == 'INSERT') {
		this.parameter(options);
		return;
	}
	
	for (var i in this._parameters) {
		if (this._parameters[i].id === id) {
			delete this._parameters[i];
			
			if (operation != 'DELETE') {
				this.parameter(options);
			}
		}
	}
};

ParamBuilder.prototype.parameters = function () {
	var data = [];
	for (var i in this._parameters) {
		data.push(
			{
				id:    this._parameters[i].id,
				name:  i,
				type:  this._parameters[i].type,
				value: this._parameters[i].value
			}
		);
	}
	return data;
};

ParamBuilder.prototype.eval = function (expression, context) {
	if (typeof expression != 'string') {
		return expression;
	}
	
	var split = expression.split(/\[([^\[\]]+)\]/);
	for (var i in split) {
		if ((i % 2) == 1) {
			split[i] = Properties.value(split[i], context, '['+split[i]+']');
		}
	}
	
	var i = 0;
	while (i < split.length) {
		if (split[i] === '') {
			split.splice(i, 1);
		} else {
			i++;
		}
	}
	
	return split.length == 1 ?
		split[0]
		:
		split.join('')
	;
};

ParamBuilder.prototype.execute = function (userID, context) {
	var data = {};
	
	if (userID !== false) {
		data.userID = userID;
	}
	
	for (var i in this._parameters) {
		var value = this._parameters[i].value;
		data[i] = this.eval(value, context);
	}
	
	return data;
};

module.exports = function () {
	return new ParamBuilder();
};