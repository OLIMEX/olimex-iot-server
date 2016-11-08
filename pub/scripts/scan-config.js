(function ($) {
	$(document).on(
		'readyIoT',
		function () {
			$('select[name=ssid]').ssid();
			$('select[name=host]').host();
			$('.node').node();
			
			$('.scan-config').scan();
			
			$('.select-error').confirmation();
			$('.confirmation').confirmation();
		}
	);
})(jQuery);

(function ($) {
	$.fn.ssid = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $form = $this.closest('form');
				var $ssid = $form.find('input[name=custom_ssid]');
				var $psk  = $form.find('input[name=custom_psk]');
				
				$this.
					off('change.IoT').
					on(
						'change.IoT',
						function (event) {
							var val = $this.val();
							
							if (typeof event.isTrigger == 'undefined' || val) {
								$ssid.val(val);
								if (
									val &&
									typeof AccessPoints != 'undefined' && 
									typeof AccessPoints[val] != 'undefined'
								) {
									$psk.val(AccessPoints[val]);
								} else {
									$psk.val('');
								}
							}
							
							if (val) {
								$('.ssid').hide();
							} else {
								$('.ssid').show();
							}
						}
					)
				;
				
				$this.trigger('change.IoT');
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.host = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				var $form = $this.closest('form');
				var $host = $form.find('input[name=custom_host]');
				
				$this.
					off('change.IoT').
					on(
						'change.IoT',
						function (event) {
							var val = $this.val();
							if (typeof event.isTrigger == 'undefined' || val) {
								$host.val(val);
							}
							
							if (val) {
								$('.host').hide();
							} else {
								$('.host').show();
							}
						}
					)
				;
				
				$this.trigger('change.IoT');
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.node = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				var $h2     = $this.find('h2');
				var $div    = $this.find('div');
				var $config = $this.find(':checkbox');
				
				$h2.
					off('click.IoT').
					on(
						'click.IoT',
						function () {
							$config.click();
						}
					)
				;
				
				$config.
					off('change.IoT').
					on(
						'change.IoT',
						function () {
							if ($config.is(':checked')) {
								$div.show();
								$this.addClass('config')
							} else {
								$div.hide();
								$this.removeClass('config')
							}
						}
					)
				;
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.scan = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				$this.
					off('submit.IoT').
					on(
						'submit.IoT',
						function (event, scan) {
							if (scan) {
								return true;
							}
							
							if ($('.node :checked').length == 0) {
								$('.select-error').trigger('confirm', $this);
								return false;
							}
							
							$('.confirmation').trigger('confirm', $this);
							return false;
						}
					)
				;
			}
		);
	};
})(jQuery);
