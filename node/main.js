var Config       = require('./lib/config');
var Connections  = require('./lib/connections');

var BasicAuth    = require('basic-auth');
var jsonParser   = require('body-parser').json();

var Application  = require('express')();

var httpServer   = require('http').Server(Application);
httpServer.listen(
	Config.system('listen.port'), 
	Config.system('listen.host'),
	function() {
		console.log('NodeJS listening on '+Config.system('listen.host')+':'+Config.system('listen.port'));
	}
);

var websocket = new (require('websocket').server)(
	{
		httpServer: httpServer,
		autoAcceptConnections: false
	}
);

websocket.on(
	'request', 
	function(request) {
		console.log('WEBSOCKET: Connection request ['+request.resource+']');
		Connections.add(request);
	}
);

var JSONPath     = require('advanced-json-path');
var Properties   = require('./lib/properties');
var Actions      = require('./lib/actions');
var Filters      = require('./lib/filters');
var Triggers     = require('./lib/triggers');
var Authenticate = require('./lib/authenticate');

function authFail(response) {
	response.status(401);
	response.setHeader('WWW-Authenticate', 'Basic realm="Authentication required"');
	response.send(
		{Status : "Access denied"}
	);
}

function processHeaders(request, response, callback) {
	var realIP = JSONPath(request, '$.headers["x-real-ip"]');
	realIP = realIP ? realIP : request.remoteAddress;
	
	console.log('HTTP '+request.method+': ['+request.url+'] ['+realIP+']');
	var details = {
		IP: realIP,
		Method: request.method,
		URL: request.url
	};
	
	var credentials = BasicAuth(request);
	if (typeof credentials == 'object') {
		Authenticate.user(
			{
				User:     credentials.name,
				Password: credentials.pass
			},
			function (user) {
				if (user instanceof Error) {
					authFail(response);
					return;
				}
				callback(user, details);
			}
		);
		return;
	}
	
	if (request.headers.api) {
		Authenticate.api(
			request.headers.api, 
			Config.system('pg'),
			function (user) {
				if (user instanceof Error) {
					authFail(response);
					return;
				}
				
				callback(user, details);
			}
		);
		return;
	}
	
	authFail(response);
}

function responseHeaders(response) {
	response.setHeader('Access-Control-Allow-Origin', '*');
	response.setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type');
}

Application.use(
	function(request, response, next) {
		var match = request.originalUrl.match(/(.*)\/api-key\/([A-Za-z0-9]+)$/);
		if (match) {
			request.url =  match[1];
			request.headers.api = match[2];
		}
		
		next();
	}
);

Application.options(
	'*', 
	function(request, response) {
		console.log('OPTIONS ['+request.url+']');
		responseHeaders(response);
		response.end();
	}
);

Application.get(
	'/api/nodes(/[0-9]+)?', 
	function(request, response) {
		var idMatch = request.url.match(/[0-9]+/);
		var id = null;
		if (idMatch) {
			id = Number(idMatch[0]);
		}
		
		processHeaders(
			request, 
			response,
			function (user) {
				responseHeaders(response);
				response.send(Connections.getNodes(user, id));
			}
		);
	}
);

Application.get(
	'/api/properties', 
	function(request, response) {
		processHeaders(
			request, 
			response,
			function () {
				responseHeaders(response);
				response.send(Properties.all());
			}
		);
	}
);

Application.get(
	'/api/actions', 
	function(request, response) {
		processHeaders(
			request, 
			response,
			function () {
				responseHeaders(response);
				response.send(Actions.all());
			}
		);
	}
);

Application.get(
	'/api/filters', 
	function(request, response) {
		processHeaders(
			request, 
			response,
			function () {
				responseHeaders(response);
				response.send(Filters.all());
			}
		);
	}
);

Application.get(
	'/api/triggers', 
	function(request, response) {
		processHeaders(
			request, 
			response,
			function (user) {
				responseHeaders(response);
				response.send(Triggers.all(user));
			}
		);
	}
);

Application.get(
	'*', 
	function(request, response) {
		processHeaders(
			request, 
			response,
			function () {
				responseHeaders(response);
				response.status(404);
				response.send(
					{Status : "Not found"}
				);
			}
		);
	}
);

Application.post(
	'/api/nodes/[0-9]+', 
	jsonParser, 
	function(request, response) {
		var idMatch = request.url.match(/[0-9]+/);
		var id = null;
		if (idMatch) {
			id = Number(idMatch[0]);
		} else {
			response.status(400);
			response.send(
				{Status : "Invalid request. Missing node id."}
			);
			return;
		}
		
		processHeaders(
			request, 
			response,
			function (user) {
				if (request.body) {
					var status = Connections.post(user.id, id, request.body);
					
					responseHeaders(response);
					response.send(
						{Status : status}
					);
					return;
				}
				
				console.log('POST: Invalid request');
				response.status(400);
				response.send(
					{Status : "Invalid request"}
				);
			}
		);
	}
);

Application.post(
	'*', 
	jsonParser, 
	function(request, response) {
		processHeaders(
			request, 
			response,
			function (user, info) {
				if (request.body) {
					var status = Connections.action(user.id, request.body, info);
					
					responseHeaders(response);
					response.send(
						{Status : status}
					);
					return; 
				}
				
				console.log('POST: Invalid request');
				response.status(400);
				response.send(
					{Status : "Invalid request"}
				);
			}
		);
	}
);

