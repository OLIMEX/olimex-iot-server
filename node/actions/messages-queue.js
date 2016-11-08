var Connections = require('../lib/connections');
var Triggers    = require('../lib/triggers');

const MESSAGES_TIMEOUT     = 60000;  // 1 minute
const MESSAGES_DEF_TIMEOUT =  5000;  // 5 seconds

/****************************************************************************
 * Message Queue
 ****************************************************************************/
function MessagesQueue(userID, node, url, rows, cols, speed, r, g, b) {
	this._userID = userID;
	this._token = node;
	this._connection = null;
	
	this._queue = [];
	this._timeout = null;
	
	this._url = url;
	
	this._rows = rows;
	this._cols = cols;
	this._speed = speed;
	
	this._r = typeof r == 'undefined' ? 1 : r;
	this._g = typeof g == 'undefined' ? 1 : g;
	this._b = typeof b == 'undefined' ? 1 : b;
	
	this._default = null;
	this._default_timeout = null;
	
	this._current = null;
	this._triggers = [];
	
	this.connect();
	this.post();
}

MessagesQueue.prototype.addTrigger = function (trigger) {
	this._triggers.push(trigger);
	return this;
}

MessagesQueue.prototype.removeTriggers = function () {
	// remove global registered triggers
	this._triggers.forEach(
		function (trigger) {
			Triggers.remove(trigger);
		}
	);
	
	// clear local references
	this._triggers = [];
	return this;
}

MessagesQueue.prototype.connect = function () {
	if (this._connection === null) {
		var connection = Connections.findNode(this._userID, this._token);
		if (connection !== null) {
			this._connection = connection;
			this._token = connection.node.Token;
		}
	}
	
	return this;
};

MessagesQueue.prototype.url = function () {
	return this._url;
}

MessagesQueue.prototype.token = function () {
	return this._token;
};

MessagesQueue.prototype.rows = function (rows) {
	this._rows = rows;
	return this;
};

MessagesQueue.prototype.cols = function (cols) {
	this._cols = cols;
	return this;
};

MessagesQueue.prototype.speed = function (speed) {
	this._speed = speed;
	return this;
};

MessagesQueue.prototype.defaultMsg = function (msg) {
	this._default = msg;
	this.next();
};

MessagesQueue.prototype.display = function (message) {
	this._queue.push(message);
	this.next();
	return this;
};

MessagesQueue.prototype.post = function(data) {
	if (typeof this._connection != 'object' || !this._connection.connected) {
		return;
	}
	
	if (typeof data == 'undefined') {
		data = {};
	}
	
	var ref = typeof data.ref == 'undefined' ? 
		null 
		: 
		data.ref
	;
	
	this._connection.sendUTF(
		JSON.stringify(
			{
				URL: this._url,
				Method: 'POST',
				ref: ref,
				Data: {
					Text: typeof data.text == 'undefined' ?
						''
						:
						data.text
					,
					R: typeof data.r == 'undefined' ?
						this._r
						:
						data.r
					,
					G: typeof data.g == 'undefined' ?
						this._g
						:
						data.g
					,
					B: typeof data.b == 'undefined' ?
						this._b
						:
						data.b
					,
					Speed: typeof data.speed == 'undefined' ? 
						this._speed
						:
						data.speed
					,
					cols: typeof data.cols == 'undefined' ?
						this._cols
						:
						data.cols
					,
					rows: typeof data.rows == 'undefined' ? 
						this._rows
						:
						data.rows
				}
			}
		)
	);
}

MessagesQueue.prototype.next = function() {
	if (this._timeout !== null) {
		return;
	}
	
	var self = this;
	if (this._queue.length == 0) {
		if (this._default === null) {
			return;
		}
		// display default message after timeout
		this._default_timeout = setTimeout(
			function () {
				self.display(self._default);
			},
			MESSAGES_DEF_TIMEOUT
		);
		return;
	}
	
	this.connect();
	if (typeof this._connection != 'object' || !this._connection.connected) {
		// retry after timeout
		setTimeout(
			function () {
				self.next();
			},
			MESSAGES_TIMEOUT
		);
		return;
	}
	
	this.setTimeout();
	this._current = this._queue.shift();
	this.post(this._current);
};

MessagesQueue.prototype.setTimeout = function () {
	var self = this;
	
	if (this._default_timeout !== null) {
		// reset default message timout
		clearTimeout(this._default_timeout);
		this._default_timeout = null;
	}
	
	if (this._timeout !== null) {
		clearTimeout(this._timeout);
	}
	
	this._timeout = setTimeout(
		// done after timeout
		function () {
			self.done();
		},
		MESSAGES_TIMEOUT
	);
};

MessagesQueue.prototype.done = function () {
	if (this._timeout !== null) {
		clearTimeout(this._timeout);
	}
	this._current = null;
	this._timeout = null;
	this.next();
};

MessagesQueue.prototype.busy = function () {
	if (this._current !== null) {
		if (
			this._default === null || 
			this._current.text != this._default.text
		) {
			this._queue.unshift(this._current);
		}
		this._current = null;
	}
	this.setTimeout();
};

module.exports = MessagesQueue;