var JSONPath     = require('advanced-json-path');
var Config       = require('./config');
var Authenticate = require('./authenticate');
var Properties   = require('./properties');
var Log          = require('./log');
var State        = require('./state');
var Actions      = require('./actions');
var Triggers     = require('./triggers');

function arrayRemove(a, e) {
	var i = a.indexOf(e);
	if (i >= 0) {
		a.splice(i, 1);
	}
}

function jsonMessage(message) {
	var data = null;
	
	if (message.type === 'binary') {
		console.log('Received Binary Message of %d bytes', message.binaryData.length);
		return data;
	}
	
	if (message.type !== 'utf8') {
		console.log('Unknown message type [%s]', message.type);
		return data;
	}
	
	try {
		data = JSON.parse(message.utf8Data);
	} catch (error) {
		console.log('JSON Error ['+error+']');
		console.log('ErrorData: '+message.utf8Data);		
	}
	
	return data;
}

/****************************************************************************
 * Node - WebSocket Node connection handling
 ****************************************************************************/
function Node(connection, realIP) {
	connection.node = this;
	
	this.connection = connection;
	
	this.id         = null;
	this.IP         = realIP;
	this.Port       = connection.socket._peername.port;
	this.Name       = null;
	this.Token      = null;
	this.About      = null;
	
	this.Devices    = [];
	
	console.log('NODE: Connection accepted '+this.toString());
	
	var self = this;
	setTimeout(
		function () {
			if (!self.authorized()) {
				self.connection.close(1008, 'Not authorized within timeout interval');
			}
		},
		5000
	);
	
	connection.on(
		'message',
		function (message) {
			var data = jsonMessage(message);
			if (data === null) {
				return;
			}
			
			if (JSONPath(data, '$[?($.User)][?($.Password)]')) {
				// Node authentication
				this.node.authenticate(data);
				return;
			}
			
			if (JSONPath(data, '$[?($.Name)][?($.Token)]')) {
				// Node identification
				this.node.setToken(data);
				return;
			}
			
			if (!this.node.authorized()) {
				console.log('NODE (unauthorized): %j', data);
				return;
			}
			
			data.EventNode = this.node.get();
				
			var devices = JSONPath(data, '$[?($.EventURL == "/devices")].EventData.Data.Devices[?(@.Found == 1)]');
			if (devices !== false) {
				console.log('NODE: Register devices '+this.node.toString());
				this.node.Devices = Array.prototype.concat(devices);
				this.node.getState(true);
			}
			
			var about = JSONPath(data, '$[?($.EventURL == "/config/about")].EventData.Data.About');
			if (about !== false) {
				this.node.About = about;
			}
			
			Connections.event(data);
		}
	);
	
	connection.on(
		'close', 
		function(reasonCode, description) {
			if (this.node.authorized()) {
				var data = {
					EventURL: null,
					EventData: {
						Status: 'Connection close',
						Data: {
							Reason: reasonCode+': '+description
						}
					},
					EventNode: this.node.get()
				};
				
				Connections.event(data);
				console.log('NODE: Connection close '+ this.node.toString() + ' ['+reasonCode+': '+description+']');
			} else {
				console.log('NODE: Connection close ['+reasonCode+': '+description+']');
			}
			
			Connections.remove(this);
		}
	);
}

Node.prototype.get = function (devices) {
	var data = {
		id:    this.id,
		IP:    this.IP,
		Port:  this.Port,
		Token: this.Token,
		Name:  this.Name,
		User:  this.User,
		About: this.About
	};
	
	if (devices) {
		data.Devices = this.Devices
	}
	
	return data;
};

Node.prototype.toString = function () {
	var s = '';
	
	if (this.Token) {
		s += '['+this.Token+']';
	}
	
	if (this.Name) {
		s += '['+this.Name+']';
	}
	
	if (this.IP) {
		s += '['+this.IP+':'+this.Port+']';
	}
	
	return s;
};

Node.prototype.authenticate = function (data) {
	var self = this;
	Authenticate.user(
		data,
		function (user) {
			if (user instanceof Error) {
				self.connection.close(1008, user.message);
				return;
			}
			
			self.User = user;
			
			if (typeof self.userID != 'undefined') {
				if (self.userID != user.id) {
					self.connection.close(1008, 'Token not belongs to user');
					return;
				}
				delete self.userID;
			}
			
			console.log('NODE: Authorized user '+self.toString()+' ['+user.name+']['+user.id+']');
			self.newToken();
		}
	);
};

Node.prototype.authorized = function () {
	return (
		JSONPath(this, '$[?($.User.id)][?($.id)]') !== false
	);
};

Node.prototype.accept = function () {
	if (!this.authorized()) {
		return false;
	}
	
	// Check if user config allows auto node addition
	var accept = Config.user(this.User.id, 'nodes.accept');
	if (accept == 'auto') {
		return true;
	}
	
	delete this.id;
	
	this.connection.close(1008, 'Unauthorized token ['+this.Token+']');
	return false;
}

Node.prototype.newToken = function () {
	var self = this;
	
	if (!self.authorized()) {
		return;
	}
	
	if (self.id > 0) {
		if (self.assignUser) {
			delete self.assignUser;
			if (self.accept()) {
				Authenticate.assignUser(
					self.id, self.User.id, self.Name, Config.system('pg'),
					function (result) {
						if (result instanceof Error) {
							self.connection.close(1008, result.message);
							return;
						}
						
						console.log('NODE: Token [%s] assigned to user [%s]', self.Token, self.User.name);
						
						// Request connected devices
						self.getDevices();
					}
				);
			}
			return;
		}
		
		// Request connected devices
		self.getDevices();
		return;
	}
	
	if (self.accept()) {
		Authenticate.newToken(
			self.User.id, self.Token, self.Name, Config.system('pg'),
			function (result) {
				if (result instanceof Error) {
					self.connection.close(1008, result.message);
					return;
				}
				
				console.log('NODE: New token id [%d]', result);
				self.id = result;
				
				// Request connected devices
				self.getDevices();
			}
		);
	}
};

Node.prototype.setToken = function (data) {
	var self = this;
	
	Authenticate.token(
		data.Token, Config.system('pg'),
		function (node) {
			if (typeof node == 'undefined') {
				console.log('NODE: Unknown token ['+data.Token+']');
				
				var unknownNodes = Config.system('nodes.unknown');
				if (unknownNodes == 'reject') {
					self.connection.close(1008, 'Unknown node');
					return;
				}
				
				node = {
					id: -1,
					name: data.Name,
					token: data.Token
				};
			}
			
			if (node instanceof Error) {
				self.connection.close(1008, node.message);
				return;
			}
			
			var duplicate = Connections.findNode(null, node.token);
			if (duplicate) {
				// Found duplicated token close both connections
				self.connection.close(1008, 'Duplicated token');
				duplicate.close(1008, 'Duplicated token');
				return;
			}
			
			self.id     = node.id;
			self.Name   = node.name;
			self.Token  = node.token;
			
			if (self.id > 0) {
				if (node.userID) {
					if (typeof self.User == 'undefined') {
						self.userID = node.userID;
					} else if (self.User.id !== node.userID) {
						self.connection.close(1008, 'Token not belongs to user');
						return;
					}
				} else {
					self.assignUser = true;
					self.Name = data.Name;
				}
				console.log('NODE: Authorized token '+self.toString());
			}
			
			self.newToken();
		}
	);
};

Node.prototype.getDevices = function () {
	if (!this.authorized()) {
		return;
	}
	
	// About node
	this.connection.sendUTF(
		JSON.stringify(
			{
				URL: '/config/about',
				Method: 'GET'
			}
		)
	);
	
	// Request connected devices
	this.connection.sendUTF(
		JSON.stringify(
			{
				URL: '/devices',
				Method: 'GET'
			}
		)
	);
};

Node.prototype.getState = function (log) {
	if (!this.authorized()) {
		return;
	}
	
	var self = this;
	this.Devices.forEach(
		function (device, index) {
			setTimeout(
				function () {
					var request = {
						URL: device.URL,
						Method: 'GET',
					};
					if (typeof log == 'undefined' || log == false) {
						request.ref = 'get-state';
					}
					self.connection.sendUTF(
						JSON.stringify(request)
					);
				},
				1000 + 500 * index
			);
		}
	);
};

/****************************************************************************
 * Client - WebSocket Client connection handling
 ****************************************************************************/
function Client(connection, realIP) {
	var client = this;
	connection.client = this;
	
	this.connection = connection;
	this.cookies = [];
	
	this.IP   = realIP;
	this.Port = connection.socket._peername.port;
	
	console.log('CLIENT: Connection accepted '+this.toString());
	
	connection.on(
		'message',
		function (message) {
			var data = jsonMessage(message);
			if (data === null) {
				return;
			}
			
			client.authorized(
				function () {
					Connections.command(client.User.id, data);
				},
				
				function () {
					connection.close(1008, 'Unauthorized client message');
				}
			);
		}
	);
	
	connection.on(
		'close', 
		function(reasonCode, description) {
			console.log('CLIENT: Close '+this.client.toString()+' ['+reasonCode+': '+description+']');
			Connections.remove(this);
		}
	);
}

Client.prototype.send = function (message) {
	var connection = this.connection;
	this.authorized(
		function () {
			connection.sendUTF(message);
		},
		
		function () {
			connection.close(1008, 'Unauthorized client connection');
		}
	);
};

Client.prototype.toString = function () {
	var s = '';
	
	if (this.IP) {
		s += '['+this.IP+':'+this.Port+']';
	}
	
	return s;
};

Client.prototype.setUser = function (user, cookies) {
	if (user) {
		console.log('CLIENT: Authorized user '+this.toString()+' ['+user.name+']['+user.id+']');
		this.User = user;
		if (typeof cookies != 'undefined') {
			this.cookies = cookies
		}
		this.getState();
		return;
	}
	
	this.connection.close(1008, 'Unauthorized user');
};

Client.prototype.authorized = function (authorized, unauthorized) {
	var userID = JSONPath(this, '$.User.id');
	
	if (userID === false) {
		unauthorized();
		return;
	}
	Authenticate.cookie(userID, this.cookies, authorized, unauthorized);
};

Client.prototype.getState = function () {
	var connection = this.connection;
	State.get(this.User.id).forEach(
		function (data, index) {
			setTimeout(
				function () {
					connection.sendUTF(
						JSON.stringify(data)
					)
				},
				100 + index * 50
			)
		}
	);
};

/****************************************************************************
 * WebSocket Connection polls
 ****************************************************************************/
var Connections = module.exports = {
	_nodes_poll: [],
	_clients_poll: [],
	
	add: function (request) {
		var realIP = JSONPath(request, '$.httpRequest.headers["x-real-ip"]');
		realIP = realIP ? realIP : connection.remoteAddress;
		
		var self = this;
		
		if (request.resource === '/events') {
			var connection = request.accept();
			new Node(connection, realIP)
			this._nodes_poll.push(connection);
			return;
		}
		
		if (request.resource === '/clients') {
			Authenticate.cookie(
				null, request.cookies,
				
				function (response) {
					var connection = request.accept();
					
					self._clients_poll.push(connection);
					
					var client = new Client(connection, realIP);
					client.setUser(JSONPath(response, '$.User'), request.cookies);
				},
				
				function () {
					console.log('CONNECTIONS: Request rejected');
					request.reject();
				}
			);
			return;
		}
		
		console.log('CONNECTIONS: Rquest rejected [Invalid URL]');
		request.reject();
	},
	
	findNode: function (userID, node) {
		for (var index in this._nodes_poll) {
			var current = this._nodes_poll[index];
			
			if (userID && JSONPath(current, '$.node.User.id') != userID) {
				continue;
			}
			
			if (
				current.node.Name  == node ||
				current.node.Token == node
			) {
				return current;
			}
		}
		
		return null;
	},
	
	changeNode: function (operation, data) {
		if (operation == 'DELETE') {
			this.remove(
				this.findNode(null, data.token),
				1008, 'Node deleted'
			);
			return;
		}
	},
	
	remove: function (connection, code, reason) {
		if (connection && typeof connection == 'object') {
			if (connection.connected) {
				connection.close(code, reason);
				return;
			}
			
			arrayRemove(this._nodes_poll, connection);
			arrayRemove(this._clients_poll, connection);
		}
	},
	
	getNodes: function (user, id) {
		var data = [];
		
		var userID = JSONPath(user, '$.id');
		if (userID === false) {
			return data;
		}
		
		this._nodes_poll.forEach(
			function (current) {
				if (JSONPath(current, '$.node.User.id') !== userID) {
					return;
				}
				
				if (
					typeof id == 'undefined' || 
					id === null || 
					id === current.node.id
				) {
					data.push(
						current.node.get(true)
					);
				}
			}
		);
		return data;
	},
	
	event: function (data) {	
		var self = this;
		var userID = Properties.value('UserID', data);
		
		var changes = State.update(data);
		Log.record(
			data, Config.system('pg'),
			function (refID) {
				Triggers.fire(data, changes, refID);
			}
		);
		
		if (userID === false) {
			return;
		}
		
		// clone data 
		var client_data = JSON.parse(JSON.stringify(data));
		delete client_data.EventNode.User;
		
		this._clients_poll.forEach(
			function (current) {
				if (JSONPath(current, '$.client.User.id') === userID) {
					current.client.send(
						JSON.stringify(client_data)
					);
				}
			}
		);
	},
	
	command: function (userID, data) {
		var token = Properties.value('NodeToken', data);
		
		if (token === false) {
			console.log('CONNECTIONS: Command missing token');
			return;
		}
		
		var connection = this.findNode(userID, token);
		
		if (connection == null) {
			console.log('CONNECTIONS: Node is not connected ['+token+']');
			this.error('Node is not connected ['+token+']', userID);
			return;
		}
		
		if (connection.node.User.id !== userID) {
			console.log('CONNECTIONS: User ID do not match');
			this.error('User ID do not match', userID);
			return;
		}
		
		data.EventNode.id = connection.node.id;
		Log.record(
			{
				Command:   data.EventData,
				EventNode: data.EventNode
			}, 
			Config.system('pg'),
			function (refID) {
				var command = JSONPath(data, '$.EventData.Command');
				if (command !== false) {
					switch (command) {
						case 'Close connection' :
							connection.close(1008, 'Connection closed by user');
						break;
					}
					return;
				}
				
				data.EventData.ref = refID;
				connection.sendUTF(
					JSON.stringify(
						data.EventData
					)
				);
			}
		);
	},
	
	post: function (userID, nodeID, data) {
		
		var node = null;
		for (var i in this._nodes_poll) {
			if (
				JSONPath(this._nodes_poll[i], '$.node.id') == nodeID && 
				JSONPath(this._nodes_poll[i], '$.node.User.id') == userID
			) {
				node = this._nodes_poll[i].node;
				break;
			}
		}
		
		if (node === null) {
			console.log('CONNECTIONS: POST invalid nodeID');
			return 'Invalid nodeID';
		}
		
		console.log('CONNECTIONS: POST %j', data);
		node.connection.sendUTF(
			JSON.stringify(
				data
			)
		);
		
		return 'OK';
	},
	
	action: function (userID, data, request) {
		var actionName = JSONPath(data, '$.Action');
		if (actionName === false) {
			console.log('CONNECTIONS: Action name is missing');
			return 'Action name is missing';
		}
		
		var parameters = JSONPath(data, '$.Parameters');
		if (parameters === false) {
			console.log('CONNECTIONS: Parameters are missing');
			return 'Parameters are missing';
		}
		
		var action = Actions.get(actionName);
		if (action === null) {
			console.log('CONNECTIONS: Action not found ['+actionName+']');
			return 'Action not found ['+actionName+']';
		}
		
		var self = this;
		parameters.userID = userID;
		console.log('ACTION: %j', parameters);
		
		Log.record(
			{
				Action: {
					Action:     actionName,
					Parameters: parameters,
					Request:    request
				}
			},
			Config.system('pg'),
			function (refID) {
				try {
					parameters.ref = refID;
					action.execute(parameters);
				} catch (err) {
					self.error(err, userID);
				}
			}
		);
		
		return 'OK';
	},
	
	error: function (err, userID) {
		console.log('ERROR: '+String(err));
		Connections.event(
			{
				EventData: {
					Device: 'IoT-Server',
					Error: String(err)
				},
				
				EventNode: {
					User: {
						id: userID
					}
				}
			}
		);
	}
	
};
