var http  = require("http");
var https = require("https");

module.exports = {
	request: function(options, callback) {
		if (typeof options.post == 'object') {
			options.post = JSON.stringify(options.post);
		}
		
		if (typeof options.method == 'undefined') {
			options.method = (options.post ? 
				'POST'
				:
				'GET'
			);
		}
		
		if (typeof options.headers == 'undefined') {
			options.headers = {};
		}
		
		if (options.cookies) {
			if (typeof options.cookies == 'string') {
				options.headers.Cookie = options.cookies;
			} else {
				try {
					var cookies = '';
					options.cookies.forEach(
						function (cookie) {
							if (cookies != '') {
								cookies += '; ';
							}
							cookies += encodeURIComponent(cookie.name)+'='+encodeURIComponent(cookie.value);
						}
					);
					options.headers.Cookie = cookies;
				} catch (error) {
					callback(error);
					return;
				}
			}
			delete options.cookies;
		}
		
		var secure = 
			(typeof options.secure == 'boolean' && options.secure) ||
			(typeof options.https  == 'boolean' && options.https)		
		;
		
		var protocol = (secure ?
			https
			:
			http
		);
		
		if (options.debug) {
			console.log(
				'%s: http%s://%s%s', 
				options.method,
				secure ? 's' : '',
				options.host,
				options.path
			);
		}
		
		var request = protocol.request(
			options,
			
			function(response) {
				var output = '';
				
				response.setEncoding('utf8');
				
				response.on(
					'data', 
					function (chunk) {
						output += chunk;
					}
				);
				
				response.on(
					'end', 
					function() {
						callback(response.statusCode, output);
					}
				);
			}
		);

		request.on(
			'error', 
			function(error) {
				console.log('REST: '+error);
				callback(error);
			}
		);
		
		if (options.post) {
			if (options.debug) {
				console.log(options.post);
			}
			request.write(options.post);
		}
		
		request.end();
	}
};