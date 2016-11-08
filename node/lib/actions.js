var OperationsPoll = require('./operations/poll');

/****************************************************************************
 * Actions
 ****************************************************************************/
ActionsPoll.prototype = new OperationsPoll();
ActionsPoll.prototype.constructor = ActionsPoll;

function ActionsPoll(name) {
	OperationsPoll(name);
}

ActionsPoll.prototype.all = function () {
	var data = [];
	for (var i in this._poll) {
		if (this._poll[i].internal) {
			continue;
		}
		
		data.push(
			{
				Action:      this._poll[i].name,
				Description: this._poll[i].description,
				Parameters:  this._poll[i].parameters()
			}
		);
	}
	return data;
};

var Actions = module.exports = new ActionsPoll('ACTIONS');
