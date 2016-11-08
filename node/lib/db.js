var pg = require('pg');

function Database(config) {
	if (typeof config == 'undefined') {
		throw new Error('Missing database configuration');
	}
	
	this.config = config;
}

Database.prototype.query = function(query, callback) {
	pg.connect(
		this.config,
		function(error, client, done) {
			if (error) {
				callback(error);
				return;
			}
			
			client.query(
				query, 
				function(error, result) {
					done();
					
					if (error) {
						callback(error);
						return;
					}
					callback(result);
				}
			);
		}
	);
}

Database.prototype.listen = function(channel, callback) {
	pg.connect(
		this.config,
		function(error, client, done) {
			if (error) {
				callback(error);
				return;
			}
			client.on('notification', callback);
			client.query('LISTEN ' + channel);
		}
	);
}

// Databases
module.exports = {
	_poll: {},
	
	connect: function (name, config) {
		if (typeof this._poll[name] != 'undefined') {
			return this._poll[name];
		}
		
		this._poll[name] = new Database(config);
		return this._poll[name];
	},
	
	get: function (name) {
		if (typeof this._poll[name] == 'undefined') {
			throw 'Unknown Database ['+name+']';
		}
		
		return this._poll[name];
	}
}
