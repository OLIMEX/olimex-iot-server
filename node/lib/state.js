var JSONPath   = require('advanced-json-path');
var Properties = require('./properties');

function isEmpty(a) {
	if (typeof a != 'object') {
		return a == null;
	}
	
	for (var i in a) {
		return false;
	}
	
	return true;
}

/****************************************************************************
 * merge - merge object b into object a returning differences
 ****************************************************************************/
function merge(a, b) {
	var c = {
		before: {},
		after:  {}
	};
	
	if (typeof a != 'object' || typeof b != 'object') {
		return c;
	}
	
	for (var i in b) {
		if (typeof b[i] == 'object') {
			if (typeof a[i] != 'object') {
				c.before[i] = {};
				a[i] = {}
			}
			var x = merge(a[i], b[i]);
			if (!isEmpty(x.after)) {
				c.before[i] = x.before;
				c.after[i]  = x.after;
			}
			continue;
		}
		
		if (typeof a[i] == 'undefined') {
			a[i] = null;
		}
		
		if (a[i] != b[i]) {
			c.before[i] = a[i];
			a[i] = b[i];
			c.after[i] = b[i];
		}
	}
	
	return c;
}

function extract(a, p) {
	p = p.split('.');
	
	var r = {};
	var i = p.shift();
	
	if (typeof a[i] == 'undefined') {
		return r;
	} else if (typeof a[i] == 'object') {
		r[i] = {};
		if (p.length == 0) {
			merge(r[i], a[i]);
		} else {
			r[i] = extract(a[i], p.join('.'))
		}
	} else {
		r[i] = a[i]
	}
	return r;
}

/****************************************************************************
 * State
 ****************************************************************************/
module.exports = {
	_state: {},
	
	update: function (data) {
		var url = Properties.value('EventURL', data);
		if (
			url == '/devices' || 
			url == '/config/about' || 
			url == '/config/station' || 
			url === false
		) {
			// Register new node connection or no url defined - broadcast message
			return {
				before: {},
				after: data
			};
		}
		
		var userID = Properties.value('UserID', data);
		if (userID === false) {
			// No user defined - broadcast message
			userID = -1;
		}
		
		var token  = Properties.value('NodeToken', data);
		if (token === false) {
			// No token defined - broadcast message
			token = -1;
		}
		
		var status = Properties.value('Status', data);
		var error  = Properties.value('Error',  data);
		
		if (typeof this._state[userID] == 'undefined') {
			this._state[userID] = {};
		}
		
		if (typeof this._state[userID][token] == 'undefined') {
			this._state[userID][token] = {};
		}
		
		if (status == 'Connection close') {
			// console.log('STATE: Remove [%s]', token);
			delete this._state[userID][token];
			return {
				before: {},
				after: data
			};
		}
		
		if (typeof this._state[userID][token][url] == 'undefined') {
			this._state[userID][token][url] = data;
			return {
				before: {},
				after: {}
			};
		}
		
		var changes = merge(this._state[userID][token][url], data);
		
		merge(changes.after,  extract(data, 'EventData.Device'));
		merge(changes.after,  extract(data, 'EventNode'));
		
		if (status === false ) {
			delete this._state[userID][token][url]['EventData']['Status'];
		}
		
		if (error === false ) {
			delete this._state[userID][token][url]['EventData']['Error'];
		}
		
		return changes;
	},
	
	get: function (userID, token, url) {
		if (typeof userID == 'undefined') {
			return [];
		}
		
		var path = '$.'+userID;
		path += (token ? '.'+token : '.*');
		path += (url   ? '.' +url  : '.*');
		
		var data = JSONPath(this._state, path);
		if (data === false) {
			return [];
		}
		
		return Array.prototype.concat(data);
	}
};
