(function ($) {
	$(document).on(
		'readyIoT',
		function () {
			$('.nodes').nodes();
			$('.node').node();
			$('.formIoT').formIoT();
			$('.about').about();
			
			$('.integer').integers();
			$('.number, .milli, .deci, .normalize, .kWh').floats();
			$('.catch').directConnect();
		}
	);
})(jQuery);

var connection = null;

function nodeSend(token, data) {
	if (connection === null) {
		return;
	}
	
	connection.send(
		JSON.stringify(
			{
				EventNode: {
					Token: token
				},
				
				EventData: data
			}
		)
	);
	
	return false;
}

function nodeReconnect(token) {
	nodeSend(
		token, 
		{
			Command: 'Close connection'
		}
	);
}

function nodeRestart(token) {
	nodeSend(
		token, 
		{
			URL:    '/config/about', 
			Method: 'POST',
			Data: {
				Restart: 1
			},
			ref:    'ClientRestart'
		}
	);
}

function nodeStation(token) {
	nodeSend(
		token, 
		{
			URL:    '/config/station', 
			Method: 'GET',
			ref:    'Client'
		}
	);
}

function nodeAbout(token) {
	nodeSend(
		token, 
		{
			URL:    '/config/about', 
			Method: 'GET',
			ref:    'Client'
		}
	);
}

(function ($) {
	$.iotEventsListen = function (url) {
		connection = new WebSocket(url);
		
		connection.onopen = function () {
		};
		
		connection.onmessage = function (event) {
			try {
				var message = JSON.parse(event.data);
				$('.eventIoT').trigger('eventIoT', message);
			} catch (e) {
				// console.log(e);
			} 
		};
		
		connection.onclose = function (event) {
			if (event.reason.match(/unauthorized/i)) {
				window.location.reload();
				return;
			}
			
			$('.node').trigger(
				'eventIoT', 
				{
					EventData: {
						Status: 'Close all',
						Data: {
							Reason: event.code+': '+event.reason
						}
					}
				}
			);
			
			setTimeout(
				function restoreConnection() {
					var $document = $(document);
					$.ajax(
						{
							url:     '/nodes',
							type:    'GET',
							timeout: 3000
						}
					).done(
						function (data, status) {
							if (status != 'success') {
								return;
							}
							$('.nodes').replaceWith(data);
							$document.trigger('readyIoT');
							$.iotEventsListen(url);
						}
					).error(
						function () {
							setTimeout(
								restoreConnection,
								3000
							);
						}
					);
				},
				2000
			);
		};
	};
})(jQuery);

(function ($) {
	$.fn.checkboxIoT = function () {
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $form = $this.closest('form');
				
				$this.off('change.IoT');
				$this.on(
					'change.IoT',
					function (event) {
						var data = {};
						$this.data('refresh', false);
						data[$this.attr('name')] = parseInt($this.is(':checked') * $this.val());
						$form.trigger('postIoT', data);
					}
				);
			}
		);
	}
})(jQuery);

(function ($) {
	$.fn.rangeIoT = function () {
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $form = $this.closest('form');
				var timeout = null;
				var value;
				var $text = $this.closest('form').find('input[type=text][name='+$this.attr('name')+']:disabled');
				
				$this.off('input.IoT');
				$this.on(
					'input.IoT',
					function () {
						$this.data('refresh', false);
						if ($text.length > 0) {
							$text.val($this.val());
						}
					}
				);
				
				$this.off('change.IoT');
				$this.on(
					'change.IoT',
					function (event) {
						value = $this.val();
						$this.data('refresh', false);
						if (timeout) {
							clearTimeout(timeout);
						}
						
						timeout = setTimeout(
							function () {
								$this.val(value);
								
								var data = {};
								data[$this.attr('name')] = parseInt($this.val());
								$form.trigger('postIoT', data);
								
								$this.data('refresh', true);
								timeout = null;
							},
							200
						);
					}
				);
			}
		);
	}
})(jQuery);

(function ($) {
	$.fn.serializeJSON = function() {
		var json = {};
		
		this.children().each(
			function (i, e) {
				var $e = $(e);
				if (
					$e.is(':button') || 
					$e.is(':disabled') ||
					$e.prop('disabled') ||
					($e.is(':checkbox') && $e.parents('.bits').length > 0)
				) {
					return json;
				}
				
				if ($e.is(':input')) {
					$e.trigger('toJSON');
					var val = $e.data('JSON') ? $e.data('JSON') : $e.val();
					$e.data('JSON', null);
					json[$e.attr('name')] = $e.is(':not(:checkbox)') || $e.is(':checked') ?
						(val === null ?
							null
							:
							(typeof val == 'string' && val.match(/^-?\d+$/) ?
								parseInt(val)
								:
								val
							)
						)
						:
						0
					;
					return json;
				}
				
				if ($e.attr('name') != undefined) {
					json[$e.attr('name')] = $e.serializeJSON();
				} else {
					$.extend(json, $e.serializeJSON());
				}
			}
		);
		return json;
	};
})(jQuery);

(function ($) {
	$.fn.unserializeJSON = function(json) {
		for (var name in json) {
			var i = this.find('[name="'+name+'"]');
			if (i.is(':input')) {
				if (i.is(':checkbox')) {
					if (json[name]) {
						i.prop('checked', true);
					} else {
						i.prop('checked', false);
					}
				} else {
					if (i.data('refresh') === false) {
						continue;
					}
					i.trigger('fromJSON', [json[name]]);
					i.val(i.data('JSON') ? i.data('JSON') : json[name]);
					i.data('JSON', null);
				}
				i.trigger('refresh');
			} else if (typeof json[name] === 'object') {
				i.unserializeJSON(json[name]);
			}
		}
	};
})(jQuery);

(function ($) {
	$.fn.nodes = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				$this.off('eventIoT');
				$this.on(
					'eventIoT',
					function (event, message) {
						event.stopPropagation();
						
						var nodeID = JSONPath(message, '$.EventNode.id');
						var url    = JSONPath(message, '$.EventURL');
						
						if (url === '/devices') {
							var $document = $(document);
							$.ajax(
								{
									url:    '/nodes/dashboard?id='+nodeID,
									type:   'GET'
								}
							).done(
								function (data, status) {
									if (status != 'success') {
										return;
									}
									var $node = $('#node-'+nodeID);
									if ($node.length) {
										$node.replaceWith(data);
									} else {
										$this.removeClass('center').children('.error, .guide').remove();
										$this.find('p').before(data);
									}
									
									$document.trigger('readyIoT');
								}
							);
						}
					}
				);
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.node = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				$this.off('eventIoT');
				$this.on(
					'eventIoT',
					function (event, message) {
						event.stopPropagation();
						
						var nodeID   = JSONPath(message, '$.EventNode.id');
						var status   = JSONPath(message, '$.EventData.Status');
						
						if (
							(status != 'Close all') &&
							($this.attr('id') != ('node-'+nodeID))
						) {
							return;
						}
						
						var nodeIP   = JSONPath(message, '$.EventNode.IP');
						var nodePort = JSONPath(message, '$.EventNode.Port');
						var device   = JSONPath(message, '$.EventData.Device');
						var reason   = JSONPath(message, '$.EventData.Data.Reason');
						var error    = JSONPath(message, '$.EventData.Error');
						
						var $error = device === false ?
							$this.find('.details span.error')
							:
							$this.find('.device.'+device+' .error')
						;
						
						if (error) {
							$error.html(error);
						} else {
							$error.html('');
						}
						
						if (
							status === 'Close all' || 
							(
								status === 'Connection close' && 
								$this.data('connection') === (nodeIP+':'+nodePort)
							)
						) {
							if (reason.match(/Node deleted/gi)) {
								$this.remove();
							} else {
								$this.addClass('off');
								$error.html(reason);
								$this.find('span.ip').remove();
							}
						}
					}
				);
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.formIoT = function() {
		
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				$this.find('input[type=checkbox]').checkboxIoT();
				$this.find('input[type=range]').rangeIoT();
				
				$this.off('eventIoT');
				$this.on(
					'eventIoT',
					function (event, message) {
						var token  = JSONPath(message, '$.EventNode.Token');
						var url    = JSONPath(message, '$.EventURL');
						var status = JSONPath(message, '$.EventData.Status');
						var error  = JSONPath(message, '$.EventData.Error');
						var data   = JSONPath(message, '$.EventData.Data');
						
						if ((token + url) != $this.attr('action')) {
							return;
						}
						
						event.stopPropagation();
						
						if (data !== false) {
							$this.unserializeJSON(data);
							$this.closest('.device').children('h3').each(
								function (i, e) {
									var $h3 = $(e);
									$h3.css('color', 'red');
									setTimeout(
										function () {
											$h3.css('color', '');
										}, 
										500
									);
								}
							);
						}
					}
				);
				
				$this.off('postIoT');
				$this.on(
					'postIoT',
					function (event, jsonData) {
						if (typeof jsonData == 'undefined') {
							jsonData = $this.serializeJSON();
						}
						
						var action = $this.attr('action').split('/', 2);
						var token  = action.shift();
						var url    = '/' + action.shift();
						
						nodeSend(
							token, 
							{
								URL:    url,
								Method: 'POST',
								Data:   jsonData
							}
						);
					}
				);
				
				$this.submit(
					function (event) {
						$this.trigger('postIoT');
						event.preventDefault();
						return false;
					}
				);
				
				var $device = $this.closest('.device');
				$device.children('h3').each(
					function (i, e) {
						var $h3 = $(e);
						$h3.off('click.IoT');
						$h3.on(
							'click.IoT',
							function () {
								$device.children('form, div').toggle();
							}
						);
						$h3.on(
							'click.IoT',
							'a',
							function (event) {
								event.stopImmediatePropagation();
							}
						);
					}
				);

			}
		);
	}
})(jQuery);

(function ($) {
	$.fn.about = function() {
		var $modal = $('<div class="modal" />');
		$(document.body).append($modal);
		
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $form = null;
				
				$modal.
					off('click.IoT').
					on(
						'click.IoT',
						function () {
							$this.hide();
							$modal.hide();
						}
					)
				;
				
				$this.
					off('eventIoT').
					on(
						'eventIoT',
						function (event, message) {
							var url  = JSONPath(message, '$.EventURL');
							
							if (url != $this.attr('action')) {
								return;
							}
							
							event.stopPropagation();
							var data = JSONPath(message, '$.EventData.Data');
							var ref  = JSONPath(message, '$.EventData.ref');
							
							if (
								data !== false && 
								ref == 'Client'
							) {
								data.NodeName = JSONPath(message, '$.EventNode.Name');
								
								$this.unserializeJSON(data);
								
								$modal.show();
								$this.show();
							}
						}
					).
					
					off('click.IoT hide').
					on(
						'click.IoT hide',
						function () {
							$this.hide();
							$modal.hide();
						}
					)
				;
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.directConnect = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				$this.click(
					function () {
						window.open('/direct?host='+$this.children('input').val(), '_blank');
					}
				);
			}
		);
	};
})(jQuery);
