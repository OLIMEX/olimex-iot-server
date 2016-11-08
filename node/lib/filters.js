var OperationsPoll = require('./operations/poll');

/****************************************************************************
 * Filters
 ****************************************************************************/
FiltersPoll.prototype = new OperationsPoll();
FiltersPoll.prototype.constructor = FiltersPoll;

function FiltersPoll(name) {
	OperationsPoll(name);
	this._instances = {};
}

FiltersPoll.prototype.all = function () {
	var data = [];
	for (var i in this._poll) {
		if (this._poll[i].internal) {
			continue;
		}
		
		data.push(
			{
				Filter:      this._poll[i].name,
				Description: this._poll[i].description,
				Parameters:  this._poll[i].parameters()
			}
		);
	}
	return data;
};

FiltersPoll.prototype.change = function (trigger) {
	for (var filterID in this._instances) {
		if (filterID.match(new RegExp('-'+trigger.id+'$'))) {
			delete this._instances[filterID];
		}
	}
};

FiltersPoll.prototype.apply = function (filterID, filter, params, value) {
	console.log('FILTERS: Applying ['+filterID+'] '+filter+'('+value+')');
	
	if (typeof this._instances[filterID] == 'undefined') {
		var constructor = this.get(filter);
		if (constructor === null) {
			console.log('FILTERS: ['+filter+'] not found');
			return false;
		}
		
		console.log('FILTERS: Initializing ['+filterID+'] '+filter+'(%j)', params);
		this._instances[filterID] = constructor.instance(params);
		if (this._instances[filterID] === null) {
			console.log('FILTERS: Initialization '+filterID+' failed');
			delete this._instances[filterID];
			return false;
		}
	}
	
	if (typeof this._instances[filterID].apply != 'function') {
		console.log('FILTERS: '+filter+' have no apply method');
		return false;
	}
	
	var filterValue = this._instances[filterID].apply(value);
	
	if (typeof filterValue == 'undefined') {
		console.log('FILTERS: '+filter+' does not return value');
		return false;
	}
	
	if (filterValue === false) {
		console.log('FILTERS: '+filter+' discards ['+value+']');
	}
	
	return filterValue;
}

var Filters = module.exports = new FiltersPoll('FILTERS');
