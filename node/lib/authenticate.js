var JSONPath = require('advanced-json-path');
var Config   = require('./config');
var db       = require('./db');
var REST     = require('./rest');

// Authenticate
module.exports = {
	user: function (data, callback) {
		REST.request(
			{
				host: 'localhost',
				path: '/login',
				post: data
			},
			
			function (status, response) {
				if (status instanceof Error) {
					callback(status);
					return;
				}
				
				try {
					response = JSON.parse(response);
					
					if (typeof response.Error == 'object') {
						callback(new Error(response.Error.message));
						return;
					}
					
					if (typeof response.User == 'object') {
						if (Config.user(response.User.id, 'activationCode')) {
							callback(new Error('User not activated ['+response.User.name+']'));
						} else {
							callback(response.User);
						}
						return;
					}
				} catch (error) {
					callback(error);
				}
			}
		);
	},
	
	cookie: function (userID, cookie, authorized, unauthorized) {
		REST.request(
			{
				host: 'localhost',
				path: '/users',
				cookies: cookie
			},
			
			function (status, response) {
				if (status instanceof Error) {
					console.log('Authenticate: ' + String(status));
					unauthorized(response);
					return;
				}
				
				try {
					response = JSON.parse(response);
				} catch (error) {
					console.log('Authenticate: ' + String(error));
					unauthorized(response);
					return;
				}
				
				if (JSONPath(response, '$.Status') == 'OK') {
					if (
						userID === null || 
						JSONPath(response, '$.User.id') === userID
					) {
						authorized(response);
						return;
					}
				}
				
				console.log('Authenticate: ' + JSON.stringify(response));
				unauthorized(response);
			}
		);
	},
	
	api: function (key, dbConfig, callback) {
		var dbAuthenticate = db.connect('Authenticate', dbConfig);
		dbAuthenticate.query(
			{
				text: 'SELECT * FROM "Users" WHERE "apiKey"=$1',
				values: [key]
			},
			
			function (result) {
				if (result instanceof Error) {
					console.log('AUTHENTICATE: %s', result.message);
					callback(new Error('SQL query error'));
					return;
				}
				
				if (result.rowCount == 1) {
					var user = result.rows[0];
					user.authorization = 'Basic '+ (new Buffer(user.name+':'+user.password).toString('base64'));
					callback(user);
				} else {
					callback(new Error('Unauthorized api ['+key+']'));
				}
			}
		);
	},
	
	token: function (token, dbConfig, callback) {
		var dbAuthenticate = db.connect('Authenticate', dbConfig);
		dbAuthenticate.query(
			{
				text: 'SELECT * FROM "Nodes" WHERE "token"=$1',
				values: [token]
			},
			
			function (result) {
				if (result instanceof Error) {
					console.log('AUTHENTICATE: %s', result.message);
					callback(new Error('SQL query error'));
					return;
				}
				
				if (result.rowCount == 0) {
					callback();
				} else if (result.rowCount == 1) {
					callback(result.rows[0]);
				} else {
					callback(new Error('Unauthorized token ['+token+']'));
				}
			}
		);
	},
	
	assignUser: function (tokenID, userID, name, dbConfig, callback) {
		var dbAuthenticate = db.connect('Authenticate', dbConfig);
		dbAuthenticate.query(
			{
				text: 'SELECT * FROM "Nodes" WHERE "name"=$1 AND "userID"=$2',
				values: [name, userID]
			},
			
			function (result) {
				if (result instanceof Error) {
					console.log('AUTHENTICATE: %s', result.message);
					callback(new Error('SQL query error'));
					return;
				}
				
				if (result.rowCount != 0) {
					callback(new Error('Duplicated node name ['+name+']'));
					return;
				}
				
				dbAuthenticate.query(
					{
						text: 'UPDATE "Nodes" SET "userID"=$1, "name"=$2 WHERE "id"=$3',
						values: [userID, name, tokenID]
					},
					
					function (result) {
						if (result instanceof Error) {
							console.log('AUTHENTICATE: %s', result.message);
							callback(new Error('SQL query error'));
							return;
						}
						
						if (result.rowCount != 0) {
							callback();
						}
					}
				);
			}
		);
	},
	
	newToken: function (userID, token, name, dbConfig, callback) {
		var dbAuthenticate = db.connect('Authenticate', dbConfig);
		dbAuthenticate.query(
			{
				text: 'SELECT * FROM "Nodes" WHERE "name"=$1 AND "userID"=$2',
				values: [name, userID]
			},
			
			function (result) {
				if (result instanceof Error) {
					console.log('AUTHENTICATE: %s', result.message);
					callback(new Error('SQL query error'));
					return;
				}
				
				if (result.rowCount != 0) {
					callback(new Error('Duplicated node name ['+name+']'));
					return;
				}
				
				dbAuthenticate.query(
					{
						text: 'INSERT INTO "Nodes" ("userID", "token", "name") VALUES ($1, $2, $3) RETURNING "id"',
						values: [userID, token, name]
					},
					
					function (result) {
						if (result instanceof Error) {
							console.log('AUTHENTICATE: %s', result.message);
							callback(new Error('SQL query error'));
							return;
						}
						
						if (result.rowCount != 0) {
							callback(result.rows[0].id);
						}
					}
				);
			}
		);
	}
}