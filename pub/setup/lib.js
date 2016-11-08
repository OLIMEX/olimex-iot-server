(function ($) {
	$.fn.integers = function () {
		return this.bind(
			'toJSON',
			function () {
				var $this = $(this);
				$this.data('JSON', parseInt($this.val()));
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.floats = function () {
		return this.each(
			function (i, e) {
				var $e = $(e);
				$e.data('factor', 1);
				$e.data('precision', 0);
				if ($e.is('.milli')) {
					$e.data('factor', 1000);
					$e.data('precision', 3);
				} else if ($e.is('.deci')) {
					$e.data('factor', 10);
					$e.data('precision', 1);
				} else if ($e.is('.normalize')) {
					$e.data('factor', 32767);
					$e.data('precision', 5);
				} else if ($e.is('.kWh')) {
					$e.data('factor', 3600000);
					$e.data('precision', 3);
				}
				if ($e.is('.round')) {
					$e.data('precision', 0);
				}
			}
		).
		
		bind(
			'fromJSON',
			function (event, value) {
				var $this = $(this);
				$this.data('JSON', (value / $this.data('factor')).toFixed($this.data('precision')));
			}
		).
		
		bind(
			'toJSON',
			function () {
				var $this = $(this);
				$this.data('JSON', $this.val() * $this.data('factor'));
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.status = function() {
		$.extend(
			this, {
				message : function (msg, css) {
					return this.each(
						function (i, e) {
							var $this = $(e);
							var status = $this.hasClass('status');
							
							$this.removeClass();
							$this.show();
							
							if (status) {
								$this.html(msg);
								$this.addClass('status');
							}
							
							if (css) {
								$this.addClass(css);
							}
						}
					);
				}
			}
		);
		
		return this;
	};
})(jQuery);

(function ($) {
	$.fn.wizard = function(validator) {
		var current = 0;
		var $wizard = this;
		var $back = $('button.wizard.back');
		var $next = $('button.wizard.next');
		var $submit = $('button.wizard.submit');
		var $form = $submit.closest('form');
		
		function step(offset) {
			$form.data('ready', false);
		
			var $current = $($wizard[current]);
			$current.hide();
			
			current += offset;
			
			if (current < 0) {
				current = 0;
			}
			if (current >= $wizard.length) {
				current = $wizard.length-1;
			}
			
			$current = $($wizard[current]);
			$current.show();
			$back.show();
			$next.show();
			$submit.hide();
			$current.find(':input:visible').first().focus();
			
			if (current == 0) {
				$back.hide();
			}
			if (current == $wizard.length-1) {
				$next.hide();
				$submit.show();
				$form.data('ready', true);
			}
		}
		
		this.bind(
			'restart',
			function () {
				current = 0;
				step(0);
			}
		);
		
		$form.bind(
			'next',
			function () {
				$next.trigger('click');
			}
		);
		
		$back.each(
			function (i, e) {
				$(e).click(
					function () {
						step(-1);
					}
				);
			}
		);
		
		$next.each(
			function (i, e) {
				$(e).click(
					function () {
						var v = [];
						var $current = $($wizard[current]);
						
						if (typeof validator != 'undefined') {
							$.each(
								$current.attr('class').split(/\s+/),
								function (i, css) {
									if (typeof validator[css] == 'function') {
										v.push(validator[css]);
									}
								}
							);
						}
						
						if (v.length > 0) {
							for (var i=0; i<v.length; i++) {
								v[i].apply(
									validator,
									[
										$current, 
										function () {
											step(1);
										}
									]
								);
							}
						} else {
							step(1);
						}
					}
				);
			}
		);
		
		step(0);
		return this;
	};
})(jQuery);

(function ($) {
	$.fn.DHCP = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				$this.change(
					function () {
						if ($this.is(':checked')) {
							$('#ip').hide();
						} else {
							$('#ip').show();
						}
					}
				);
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.confirmation = function() {
		var $modal = $('<div class="modal" />');
		$(document.body).append($modal);
		
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $form = null;
				
				$this.bind(
						'confirm',
						function (event, form) {
							$modal.show();
							$this.show();
							$form = $(form);
						}
					)
				;
				
				$this.find('button').each(
					function (i, e) {
						var $button = $(e);
						$button.click(
							function () {
								$this.hide();
								$modal.hide();
								
								if ($button.text() == 'Yes') {
									if ($form) {
										$form.trigger('submit', [true]);
										$form = null;
									}
								}
							}
						);
					}
				);
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.esp8266 = function() {
		var baseURL, user, password, timeout, fail;
		var Commands = $.Conveyor();
		
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $status = $this.find('.status').status();
				var timeout = 5000;
				var ssl = false;
				
				if ($this.data('timeout')) {
					timeout = $this.data('timeout') * 1000;
				}
				
				$this.find('input[name=station_dhcp]').DHCP();
				$this.find('button.again').each(
					function (i, e) {
						var $again = $(e);
						$again.click(
							function () {
								$status.hide();
								$('.success').hide();
								$('.fail').hide();
								$('.wizard').trigger('restart');
							}
						);
					}
				);
				
				var $station_ssid   = $this.find('input[name=station_ssid]');
				var $station_select = $this.find('select[name=station_select]').each(
					function (i, e) {
						$(e).change(
							function () {
								$station_ssid.val($station_select.val());
								if ($station_select.val() == '~') {
									$station_ssid.attr('type', 'text');
									$('#station_custom').show();
								} else {
									$station_ssid.attr('type', 'hidden');
									$('#station_custom').hide();
								}
							}
						);
					}
				);
				
				function init(visualise) {
					if (typeof visualise == 'undefined') {
						visualise = true;
					}
					
					baseURL   = 'http://' + $this.find('input[name=connection_host]').val();
					user      = $this.find('input[name=connection_user]').val();
					password  = $this.find('input[name=connection_password]').val();
					
					if (visualise) {
						$('.wizard').hide();
						$('.fail').hide();
						$('.progress').show();
					} else {
						$('button.wizard').hide();
					}
					
					fail = false;
					Commands.aborted = false;
				}
				
				$.extend({
					WiFiScan: function (success) {
						init(false);
						
						Commands.
							before(
								function (data) {
									$status.message('Checking connection...');
								}
							). 
							done(
								function () {
									var $ssl = $this.find('input[name=iot_ssl]');
									$ssl.prop('disabled', !ssl);
									$ssl.prop('checked',   ssl);
								}
							).
							pipe({
								retry: 2,
								websocket: false,
								longPoll: false,
								baseURL: baseURL,
								type: 'GET',
								action: '/wifi-scan',
								timeout: timeout,
								user: user,
								password: password,
								
								success: function (data) {
									$station_select.empty().append('<option value="">-- Select Network --</option>');
									if (data && data.Data && Array.isArray(data.Data.WiFi)) {
										for (var i in data.Data.WiFi) {
											$station_select.append('<option value="'+data.Data.WiFi[i].SSID+'">'+data.Data.WiFi[i].SSID+'</option>');
										}
									}
									$station_select.append('<option value="~">Custom...</option>');
								},
								
								error: function () {
									Commands.abort();
									$status.message('Failed to connect to the ESP8266 node', 'error');
									$('.wizard').trigger('restart');
								}
							}).
							pipe({
								retry: 2,
								websocket: false,
								longPoll: false,
								baseURL: baseURL,
								type: 'GET',
								action: '/config/ssl',
								timeout: timeout,
								user: user,
								password: password,
								
								success: function (data) {
									ssl = (data && data.Status && data.Status == 'OK');
									
									$status.hide();
									success();
								},
								
								error: function () {
									ssl = false;
									$status.message('Failed to connect to the ESP8266 node', 'error');
									$('.wizard').trigger('restart');
								}
							})
						;
					}
				});
				
				function commandBefore(data) {
					if (fail) {
						return;
					}
					
					if (data.before) {
						data.before(data);
					}
				}
				
				function commandSuccess(data, status, xhr) {
					if (typeof data.Error != 'undefined') {
						$status.message(data.Device+': '+data.Error, 'error');
						return;
					}
					
					if (typeof data.Status != 'undefined') {
						$status.message(data.Status, 'event');
					} else {
						$status.message('OK', 'event');
					}
					
					$this.trigger('event8266');
				}
				
				function commandDone() {
					$this.find('.progress').hide();
					if (fail) {
						$('.fail').show();
					} else {
						$this.find('.success').show();
					}
				}
				
				function commandError(status, error, xhr) {
					$status.message((status == error ? 'Error' : status)+': '+error, 'error');
					fail = true;
					
					Commands.abort();
					$('.progress').hide();
					$('.fail').show();
				}
				
				e.message = function (msg, css) {
					$status.message(msg, css);
				}
				
				$this.bind(
					'post8266',
					function (event, data) {
						Commands.
							before(commandBefore).
							done(commandDone).
							pipe({
								retry: 2,
								websocket: false,
								longPoll: false,
								baseURL: baseURL,
								type: $this.attr('method'),
								action: data.action,
								data: {Data: data.data},
								timeout: timeout,
								user: user,
								password: password,
								
								before: data.before,
								success: commandSuccess,
								error: commandError
							})
						;
					}
				);
				
				$this.submit(
					function (event, confirm) {
						event.preventDefault();
						
						if (!$this.data('ready')) {
							$this.trigger('next');
							return false;
						}
						
						if (typeof confirm == 'undefined') {
							$('.confirmation').trigger('confirm', $this);
							return false;
						} else if (!confirm) {
							return false;
						}
						
						$status.message('Submit...', 'activity');
						
						init();
						
						$this.trigger(
							'post8266',
							{
								action: '/config/iot',
								data: {
									IoT: {
										WebSocket: $this.find('input[name=iot_websocket]').is(':checked') ? 1 : 0,
										SSL:       $this.find('input[name=iot_ssl]').is(':checked') ? 1 : 0,
										Server:    $this.find('input[name=iot_server]').val(),
										Path:      $this.find('input[name=iot_path]').val(),
										User:      $this.find('input[name=user]').val(),
										Password:  $this.find('input[name=password]').val(),
										Name:      $this.find('input[name=ap_ssid]').val()
									}
								},
								before: function () {
									$status.message('Configuring IoT Server...', 'activity');
								}
							}
						);
						
						$this.trigger(
							'post8266',
							{
								action: '/config/station',
								data: {
									Station: {
										SSID:        $this.find('input[name=station_ssid]').val(),
										Password:    $this.find('input[name=station_password]').val(),
										Hostname:    $this.find('input[name=ap_ssid]').val(),
										AutoConnect: 1,
										DHCP:        $this.find('input[name=station_dhcp]').is(':checked') ? 1 : 0,
										IP: {
											Address: $this.find('input[name=station_Address]').val(),
											NetMask: $this.find('input[name=station_NetMask]').val(),
											Gateway: $this.find('input[name=station_Gateway]').val()
										}
									}
								},
								before: function () {
									$status.message('Configuring Wireless Network...', 'activity');
								}
							}
						);
						
						$this.trigger(
							'post8266',
							{
								action: '/config',
								data: {
									Config: {
										User:     $this.find('input[name=user]').val(),
										Password: $this.find('input[name=password]').val(),
									}
								},
								before: function () {
									$status.message('Configuring User...', 'activity');
								}
							}
						);
						
						$this.trigger(
							'post8266',
							{
								action: '/config/access-point',
								data: {
									AccessPoint: {
										SSID:     $this.find('input[name=ap_ssid]').val(),
										Password: $this.find('input[name=ap_password]').val(),
									}
								},
								before: function (data) {
									data.user     = $this.find('input[name=user]').val();
									data.password = $this.find('input[name=password]').val();
									
									$status.message('Configuring Access Point...', 'activity');
								}
							}
						);
						
						return false;
					}
				);
				
			}
		);
	}
})(jQuery);

/*** Requests Conveyor  */

(function ($) {
	$.Conveyor = function (settings) {
		return new Conveyor(settings);
	};
	
	function Conveyor(settings) {
		this.settings = {};
		this.queue  = new Array();
		
		$.extend(this.settings, settings);
	}

	Conveyor.prototype = {
		aborted: false,
		current: null,
		
		ajax: null,
		
		socket: null,
		
		_push: function (request) {
			if (this.aborted) {
				// console.log('_push(%s) ABORTED', request.url);
				$('form').trigger('error8266', 'Not connected');
				return;
			}
			
			if (this.current) {
				if (
					this.current.longPoll &&
					this.current.status != 'done' &&
					this.current.baseURL == request.baseURL &&
					this.current.action == request.action &&
					request.longPoll
				) {
					// console.log('_push(%s) IGNORED', request.url);
					return;
				}
			}
			
			request.status = 'waiting';
			this.queue.push(request);
			this.queue.sort(
				function (a, b) {
					return (a.longPoll - b.longPoll);
				}
			);
		},
		
		pipe: function (request) {
			// console.log('pipe(%s)', request.action);
			this._push(request);
			var conveyor = this;
			setTimeout(
				function () {
					conveyor.run();
				},
				100
			);
			return this;
		},
		
		add: function (request) {
			// console.log('add(%s)', request.action);
			this._push(request);
		},
		
		length: function () {
			return this.queue.length;
		},
		
		_abortAjax: function() {
			if (this.ajax) {
				// console.log('abort(AJAX)');
				this.ajax.abort();
				this.ajax = null;
			}
			this.queue = this.queue.filter(
				function (e) {
					return !(e.longPoll && e.status == 'waiting');
				}
			);
		},
		
		_abortSocket: function() {
			if (this.socket) {
				// console.log('abort(%s)', this.socket.url);
				this.socket.close(1000);
				this.socket = null;
			}
		},
		
		clearSocketRequests: function() {
			if (this.current && this.current.websocket) {
				this.current = null;
			}
			this.queue = this.queue.filter(
				function (e) {
					return !e.websocket;
				}
			);
		},
		
		abort: function (message) {
			this.aborted = true;
			
			this._abortAjax();
			this._abortSocket();
			
			this.queue = new Array();
			this.current = null;
			$('form').trigger('error8266', typeof message == 'undefined' ? 'Not connected' : message);
		},
		
		before: function (callback) {
			this.beforeCallback = callback;
			return this;
		},
		
		done: function (callback) {
			this.doneCallback = callback;
			return this;
		},
		
		run: function () {
			if (
				this.length() == 0 && 
				(
					this.current == null ||
					this.current.status == 'done'
				)
			) {
				if (this.doneCallback) {
					this.doneCallback();
				}
				return;
			}
			
			if (this.current) {
				if (this.current.status != 'done') {
					if (this.current.longPoll && this.length() != 0) {
						this._abortAjax();
						this.add(this.current);
					} else {
						return;
					}
				}
			}
			this.current = this.queue.shift();
			
			if (this.beforeCallback) {
				this.beforeCallback(this.current);
			}
			
			this.ajax = this._execute(this.current);
		},
		
		_execute: function (request) {
			// console.log('_execute(%s)', request.action);
			var conveyor = this;
			if (!request) {
				return;
			}
			
			if (request.websocket) {
				this._abortAjax();
				
				var data = JSON.stringify({
						URL: request.action,
						Method: request.type,
						Data: request.data
				});
				
				if (request.files) {
					data = new Blob([
						data,
						request.files
					]);
				}
				
				if (this.socket === null || this.socket.url != request.socketURL) {
					if (this.socket) {
						this.socket.close(1000);
					}
					
					this.socket = new WebSocket(request.socketURL);
					
					this.socket.onopen = function () {
						this.send(
							JSON.stringify(
								{
									User: request.user,
									Password: request.password
								}
							)
						);
						
						this.send(data);
						
						setTimeout(
							function () {
								request.status = 'done';
								conveyor.run();
							},
							100
						);
					}
					
					this.socket.onmessage = function (event) {
						try {
							$('form').trigger(request.event, JSON.parse(event.data));
						} catch (e) {
							// console.log(e.message);
							console.log(event.data);
						} 
					};
					
					this.socket.onerror = function (event) {
						console.log("WebSocket ERROR");
						console.log(event);
						// $('form').trigger('error8266', event.reason);
					};
					
					this.socket.onclose = function (event) {
						if (this.readyState != WebSocket.OPEN) {
							conveyor.socket = null;
							conveyor.clearSocketRequests();
						}
						$('form').trigger('abort8266');
						if (event.code != 1000) {
							$('form').trigger('error8266', event.code+': '+(event.reason ? event.reason : 'WebSocket error'));
						}
					};
				} else {
					if (this.socket.readyState != WebSocket.OPEN) {
						$('form').trigger('error8266', 'Not connected');
						if (this.socket.readyState != WebSocket.CONNECTING) {
							this.socket = null;
							this.clearSocketRequests();
						}
						return;
					}
					
					if (this.socket.bufferedAmount == 0) {
						this.socket.send(data);
					}
					
					setTimeout(
						function () {
							request.status = 'done';
							conveyor.run();
						},
						100
					);
				}
				return;
			}
			
			this._abortSocket();
			
			request.status = 'running';
			
			var ajax = {
				url: request.baseURL + request.action,
				type: request.type,
				timeout: request.timeout,
				headers: {
					Authorization: 'Basic '+btoa(request.user+':'+request.password)
				}
			};
			
			if (typeof request.data != 'undefined') {
				ajax.data = request.files ?
					new Blob([JSON.stringify(request.data), request.files])
					:
					JSON.stringify(request.data)
				;
				// ajax.dataType = 'json';
				ajax.contentType = false;
				ajax.processData = false;
			}
			
			return $.ajax(ajax).
			done(
				function (data, status, xhr) {
					// console.log('done(%s)', request.url);
					request.status = 'done';
					conveyor.ajax = null;
					
					if (request.success) {
						request.success(data, status, xhr);
					}
					
					conveyor.run();
				}
			).fail(
				function (xhr, status, error) {
					// console.log('fail(%s, %s, %s)', request.url, status, error);
					if (status == 'abort') {
						request.status = 'done';
						return;
					}
					
					if (error == 'Not Found') {
						error = 'URL Not Found';
					}
					
					if (xhr.status != 0 || status == 'timeout') {
						request.retry = 0;
					}
					
					if (request.retry > 0) {
						request.retry--;
						request.status = 'failed';
					} else {
						request.status = 'done';
					}
					conveyor.ajax = null;
					
					if (request.error) {
						request.error(status, error, xhr);
					}
					
					setTimeout(
						function () {
							if (request.status == 'done') {
								conveyor.run();
							} else {
								conveyor.ajax = conveyor._execute(request);
							}
						},
						100
					);
				}
			);
		}
		
	};
})(jQuery);
