/****************************************************************************
 * Operation
 ****************************************************************************/
function Operation(name, callback, description, internal) {
	this.name = name;
	this.description = description;
	this.internal = internal;
	
	if (typeof callback != 'function') {
		this.error('[' + this.name + '] callback must be a function');
		this._callback = null;
	} else {
		this._callback = callback;
	}
	
	this._parameters = {};
}

Operation.prototype.parameter = function (parameter, type, required, editor) {
	if (parameter == 'userID' || parameter == 'ref') {
		this.error(this.name+' - Parameter ['+parameter+'] is reserved for system usage.');
		return;
	}
	
	this._parameters[parameter] = {
		type: type,
		required: required,
		editor: editor
	};
	
	return this;
};

Operation.prototype.parameters = function () {
	var data = [];
	for (var i in this._parameters) {
		data.push(
			{
				Name:     i,
				Type:     this._parameters[i].type,
				Required: this._parameters[i].required,
				Editor:   this._parameters[i].editor
			}
		);
	}
	return data;
};

Operation.prototype.error = function (error) {
	console.log('OPERATION: '+error);
	throw new Error(error);
};

Operation.prototype.check = function (parameters) {
	for (var index in this._parameters) {
		var current = this._parameters[index];
		var type = typeof parameters[index];
		
		if (type == 'string' && current.type == 'boolean') {
			switch (parameters[index].toLowerCase()) {
				case '1':
				case 'true':
				case 'on':
					parameters[index] = true;
				break;
				
				case '0':
				case 'false':
				case 'off':
					parameters[index] = false;
				break;
			}
			type = typeof parameters[index];
		}
		
		if (type == 'string' && current.type == 'number') {
			parameters[index] = Number(parameters[index]);
			type = typeof parameters[index];
		}
		
		if (type == 'number' && current.type == 'string') {
			parameters[index] = parameters[index].toString();
			type = typeof parameters[index];
		}
		
		if (current.required && type == 'undefined') {
			this.error('[' + this.name + '] missing [' + index + '] parameter');
			return false;
		}
		
		if (current.type != type && type != 'undefined') {
			this.error(
				'[' + this.name + '] wrong [' + index + '] type! '+
				'Expected ['+current.type+'] got ['+type+']'
			);
			return false;
		}
	}
	
	if (typeof this._callback != 'function') {
		this.error('[' + this.name + '] wrong callback type');
		return false;
	}
	
	return true;
};

Operation.prototype.execute = function (parameters) {
	if (this.check(parameters)) {
		return this._callback(parameters);
	}
};

Operation.prototype.instance = function (parameters) {
	if (this.check(parameters)) {
		return (new this._callback(parameters));
	}
	
	return null;
};

module.exports = Operation;
