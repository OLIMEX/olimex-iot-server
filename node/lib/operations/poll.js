var Operation = require('./operation');

/****************************************************************************
 * OperationsPoll
 ****************************************************************************/
function OperationsPoll(name) {
	this._poll = {};
	this.name = typeof name != 'string' ?
		'OPERATIONS'
		:
		name.toUpperCase()
	;
}

OperationsPoll.prototype.register = function (name, callback, description, internal) {
	if (typeof this._poll[name] != 'undefined') {
		throw new Error(this.name+': Operation ['+name+'] is already registered');
	} else {
		this._poll[name] = new Operation(name, callback, description, internal);
	}
	
	return this._poll[name];
};

OperationsPoll.prototype.all = function () {
	var data = [];
	for (var i in this._poll) {
		if (this._poll[i].internal) {
			continue;
		}
		
		data.push(
			{
				Operation:   this._poll[i].name,
				Description: this._poll[i].description,
				Parameters:  this._poll[i].parameters()
			}
		);
	}
	return data;
};

OperationsPoll.prototype.get = function (name) {
	if (typeof this._poll[name] == 'undefined') {
		console.log(this.name+': Unknown action ['+name+']');
		return null;
	}
	return this._poll[name];
};

OperationsPoll.prototype.execute = function (name, parameters) {
	var action = this.get(name);
	if (action === null) {
		return;
	}
	
	return action.execute(parameters);
};

module.exports = OperationsPoll;
