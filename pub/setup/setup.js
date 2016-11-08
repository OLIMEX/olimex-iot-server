(function ($) {
	$(document).ready(
		function () {
			$.support.cors = true;
			$('#esp8266').esp8266();
			$('fieldset.wizard').wizard(
				{
					WiFiScan: function ($current, next) {
						$.WiFiScan(next);
					},
					
					NotNULL: function ($current, next) {
						var pass = true;
						var $nested = $current.find('fieldset input');
						$current.find('input,select').not($nested).each(
							function (i, e) {
								var $input = $(e);
								var len = isNaN(Number($input.data('min'))) ? 1 : Number($input.data('min'));
								if ($input.val().trim().length < len) {
									pass = false;
									$input.addClass('error');
								} else {
									$input.removeClass('error');
								}
							}
						);
						
						if (pass && typeof next == 'function') {
							next();
						}
						return pass;
					},
					
					Station: function ($current, next) {
						var DHCP = $current.find('input[name=station_dhcp]');
						var pass = this.NotNULL($current);
						if (!DHCP.is(':checked')) {
							pass = pass && this.NotNULL($current.find('#ip'));
						}
						
						if (pass && typeof next == 'function') {
							next();
						}
						return pass;
					}
				}
			);
			$('div.confirmation').confirmation();
		}
	);
})(jQuery);
