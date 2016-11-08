var TIMEOUT = 5000;

function triggerID() {
	var triggerID = $('#triggerID').val();
	return triggerID == -1 ? 
		null 
		: 
		triggerID
	;
}

(function ($) {
	var done = false;
	$(document).on(
		'readyIoT',
		function () {
			$('#nodeID').node();
			$('#filter').filter();
			$('.property-selector').propertySelector();
			
			$('form.disabled').disabled();
			
			if (done) return;
			done = true;
			
			$('#trigger-type').triggerType();
			$('#action').action();
		}
	);
})(jQuery);

(function ($) {
	$.fn.node = function () {
		var fields = [
			{ 
				selector: '#deviceID',
				url: '/devices/select'
			}, 
			
			{ 
				selector: '#propertyID',
				url: '/properties/select'
			}
		];
		
		var field = null;
		
		for (var f in fields) {
			fields[f].element = $(fields[f].selector);
			if (fields[f].element.length != 0) {
				field = fields[f];
				break;
			}
		}
		
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				if (field === null) {
					return;
				}
				
				$this.
					off('change.IoT').
					on(
						'change.IoT',
						function () {
							$.ajax(
								{
									url:  field.url,
									type: 'GET',
									data: {
										nodeID: $this.val()
									},
									timeout: TIMEOUT
								}
							).done(
								function (data, status) {
									if (status != 'success') {
										return;
									}
									field.element.replaceWith(data);
									field.element = $(field.selector);
								}
							);
						}
					)
				;
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.disabled = function () {
		return this.each(
			function (i, e) {
				$(e).find(':input').not('button, :hidden').prop('disabled', true);
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.selection = function () {
		var selection = {
			start: 0,
			end:   0
		};
		
		if (arguments.length == 0) {
			return get(this[0]);
		}
		
		if (arguments.length == 1 && typeof arguments[0] == 'object') {
			selection = arguments[0];
		} else {
			selection.start = arguments[0];
			if (arguments.length > 1) {
				selection.end = arguments[1];
			}
		}
		
		this.each(
			function (index, element) {
				set(element, selection);
			}
		);
		return this;
	};
	
	function get(element) {
		try {
			if (element.selectionStart != undefined) {
				return {
					start: element.selectionStart,
					end:   element.selectionEnd
				};
			} else if (document.selection && document.selection.createRange) {
				var range = document.selection.createRange();
				return {
					start: range.moveStart('character'),
					end:   range.moveEnd('character')
				};
			}
		} catch (err) {
			return {
				start: 0,
				end:   0
			};
		}
	}
	
	function set(element, selection) {
		try {
			if (element.setSelectionRange) {
				element.setSelectionRange(selection.start, selection.end);
			} else if (element.createTextRange) {
				var range = element.createTextRange();
				range.collapse(true);
				range.moveStart('character', selection.start);
				range.moveEnd('character', selection.end);
				range.select();
			}
		} catch (err) {
			
		}
	}
	
})(jQuery);

(function ($) {
	$.fn.triggerType = function() {
		function get(type) {
			if (!type) {
				$('#trigger-parameters').html('&nbsp;');
				return;
			}
			
			$.ajax(
				{
					url:     '/triggers/types/'+type,
					type:    'GET',
					data: {
						id: triggerID()
					},
					timeout: TIMEOUT
				}
			).done(
				function (data, status) {
					if (status != 'success') {
						return;
					}
					$('#trigger-parameters').replaceWith(data);
					$(document).trigger('readyIoT');
				}
			);
		}
		
		return this.each(
			function (i, e) {
				var $this = $(e);
				get($this.val());
				
				$this.
					off('change.IoT').
					on(
						'change.IoT',
						function () {
							get($this.val());
						}
					)
				;
			}
		);
	};
})(jQuery);

(function ($) {
	var last = false;
	$.fn.filter = function() {
		function get(name) {
			if (name === last) {
				return;
			}
			
			last = name;
			
			$.ajax(
				{
					url:     '/filters/parameters',
					type:    'GET',
					data: {
						id: triggerID(),
						name: $('#filter').val()
					},
					timeout: TIMEOUT
				}
			).done(
				function (data, status) {
					if (status != 'success') {
						return;
					}
					$('#filter-parameters').replaceWith(data);
					$(document).trigger('readyIoT');
				}
			);
		}
		
		return this.each(
			function (i, e) {
				var $this = $(e);
				get($this.val());
				
				$this.
					off('change.IoT').
					on(
						'change.IoT',
						function () {
							get($this.val());
						}
					)
				;
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.action = function() {
		function get(name) {
			$.ajax(
				{
					url:     '/actions/parameters',
					type:    'GET',
					data: {
						id: triggerID(),
						name: $('#action').val()
					},
					timeout: TIMEOUT
				}
			).done(
				function (data, status) {
					if (status != 'success') {
						return;
					}
					$('#action-parameters').replaceWith(data);
					$(document).trigger('readyIoT');
				}
			);
		}
		
		return this.each(
			function (i, e) {
				var $this = $(e);
				get($this.val());
				
				$this.
					off('change.IoT').
					on(
						'change.IoT',
						function () {
							get($this.val());
						}
					)
				;
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.propertySelector = function() {
		
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				var $for = $this.children('input');
				var $button = $this.children('div.button');
				var $selector = $this.children('div.selector');
				
				$button.
					off('click.IoT').
					on(
						'click.IoT',
						function () {
							if ($selector.is(':visible')) {
								$selector.hide();
							} else {
								$.ajax(
									{
										url:     '/properties/related',
										type:    'GET',
										data: {
											trigger: $('#trigger-type').val(),
											propertyID: $('#propertyID').val(),
											filter: $('#filter').val()
										},
										timeout: TIMEOUT
									}
								).done(
									function (data, status) {
										if (status != 'success') {
											return;
										}
										$selector.html(data);
										$(document).trigger('readyIoT');
										$selector.show();
									}
								);
							}
						}
					)
				;
				
				$selector.children().each(
					function (i, e) {
						var $this = $(e);
						$this.
							off('click.IoT').
							on(
								'click.IoT',
								function (event) {
									var select = $for.selection();
									var value = $for.val();
									$for.val(
										value.substring(0, select.start) + 
										'['+$this.text()+']'+
										value.substring(select.end)
									);
									$selector.hide();
								}
							)
						;
					}
				);
			}
		);
	};
})(jQuery);

