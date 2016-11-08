(function ($) {
	$(document).on(
		'readyIoT',
		function () {
			$(document).documentIoT();
		}
	);
	
	$(document).ready(
		function () {
			$(document).trigger('readyIoT');
		}
	);
})(jQuery);

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
				if ($e.data('factor')) {
					if ($e.data('decimals')) {
						$e.data('precision', $e.data('decimals'));
					} else {
						$e.data('precision', 0);
					}
				} else {
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
	$.urlParam = function(name) {
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		return results[1] || 0;
	};
})(jQuery);

(function ($) {
	$.fn.documentIoT = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				$this.
					off('click.IoT').
					on(
						'click.IoT',
						function (event) {
							$('.menu ul').hide();
							$('.selector').hide();
							$('.calendar').hide();
							$('.about').trigger('hide');
						}
					).
					
					off('keyup.IoT').
					on(
						'keyup.IoT',
						function (event) {
							if (event.which == 27) {
								$this.trigger('click');
							}
						}
					)
				;
				
				$('.menu').menu();
				$('.parameters').parameters();
				$('.content tr').tableLinks();
				
				$('.status').status();

				$('button[type=button]:contains(Back)').backIoT();
				$('button[type=button]:contains(Logout)').logoutIoT();
				
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.status = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $span = $this.children('span');
				
				$this.off('eventIoT');
				$this.on(
					'eventIoT',
					function (event, message) {
						var device = JSONPath(message, '$.EventData.Device');
						var error  = JSONPath(message, '$.EventData.Error');
						if (device == 'IoT-Server' && error !== false) {
							$span.removeClass();
							$span.addClass('error');
							$span.html(error);
						}
					}
				);
				
				$span.off('DOMSubtreeModified.IoT');
				$span.on(
					'DOMSubtreeModified.IoT',
					function () {
						setTimeout(
							function () {
								$span.html('&nbsp;');
							},
							5000
						);
					}
				);
				
				$span.trigger('DOMSubtreeModified.IoT');
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.backIoT = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				$this.
					off('click.IoT').
					on(
						'click.IoT',
						function () {
							window.location.href = '/back';
						}
					)
				;
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.logoutIoT = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				$this.
					off('click.IoT').
					on(
						'click.IoT',
						function () {
							window.location.href = '/logout';
						}
					)
				;
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.menu = function () {
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $ul = $this.find('ul');
				
				$this.off('click.IoT');
				$this.on(
					'click.IoT',
					function (event) {
						$('.menu').find('ul').hide();
						$ul.show();
						event.stopPropagation();
					}
				);
				
				$this.find('a').
					off('click.IoT').
					on(
						'click.IoT',
						function (event) {
							$ul.hide();
							event.stopPropagation();
						}
					)
				;
			}
		);
	}
})(jQuery);

(function ($) {
	$.fn.parameters = function () {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				$this.off('click.IoT');
				$this.on(
					'click.IoT',
					function (event) {
						var tr = $this.closest('tr');
						var active = tr.is('.active');
						
						$('.parameters').closest('tr').removeClass('active');
						$('.parameters').find('.description').hide();
						
						if (!active) {
							tr.addClass('active');
							$this.find('.description').show();
						}
					}
				);
			}
		);
	}
})(jQuery);

(function ($) {
	$.fn.tableLinks = function () {
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $a = $this.find('a').first();
				$this.
					off('click.IoT').
					on(
						'click.IoT',
						function (event) {
							if ($a.closest('.menu').length == 0) {
								var href = $a.attr('href');
								if (href && href.indexOf('http') < 0) {
									event.stopPropagation();
									window.location.href = href;
								}
							}
						}
					)
				;
			}
		);
	}
})(jQuery);

(function ($) {
	$.fn.confirmation = function() {
		var $modal = $('<div class="modal" />');
		$(document.body).append($modal);
		
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $form = null;
				var $buttons = $this.find('button');
				
				function modalClose(submit) {
					$this.hide();
					$(document).off('keydown.modal');
					
					if (submit) {
						if ($form) {
							$form.trigger('submit', [true]);
							$form = null;
							return;
						}
					}
					
					$modal.hide();
				}
				
				$this.
					off('confirm.IoT').
					on(
						'confirm.IoT',
						function (event, form) {
							$modal.show();
							$this.show();
							$buttons.last().focus();
							$form = $(form);
							
							$(document).
								off('keydown.modal').
								on(
									'keydown.modal',
									function (event) {
										if (event.keyCode == 32) { 
											// Space
											return true;
										}
										
										if (event.keyCode == 27) { 
											// ESC
											modalClose(false);
										} else if (event.keyCode == 37) { 
											// <-
											$buttons.filter(':focus').prev('button').focus();
										} else if (event.keyCode == 39) { 
											// ->
											$buttons.filter(':focus').next('button').focus();
										} else {
											var chr = String.fromCharCode(event.keyCode).toUpperCase();
											$buttons.filter(':contains('+chr+')').click();
										}
										
										event.preventDefault();
										return false;
									}
								)
							;
						}
					)
				;
				
				$buttons.each(
					function (i, e) {
						var $button = $(e);
						$button.
							off('click.IoT').
							on(
								'click.IoT',
								function () {
									modalClose($button.text() == 'Yes');
								}
							)
						;
					}
				);
			}
		);
	};
})(jQuery);

