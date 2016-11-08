(function ($) {
	$(document).on(
		'readyIoT',
		function () {
			$('.network').network();
		}
	);
})(jQuery);

(function ($) {
	$.fn.network = function () {
		var hash = window.location.hash ? 
			window.location.hash.substring(1)
			:
			'none'
		;
		
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $form = $this.find('form');
				var interfaceName = $this.find('input[name=interface]').val();
				
				if ($this.is('.'+hash)) {
					$form.show();
					$this.addClass('selected');
				}
				
				$this.find('h2').each(
					function (i, e) {
						$h2 = $(e);
						
						$h2.off('click.IoT');
						$h2.on(
							'click.IoT',
							function () {
								var visible = $form.is(':visible');
								$('.network').each(
									function (i, e) {
										var $this = $(e);
										$this.find('form').hide();
										if ($this.is('.down')) {
											$this.removeClass('selected');
										}
									}
								);
								
								if (!visible) {
									$form.show();
									$this.addClass('selected');
								}
							}
						);
					}
				);
				
				$this.find(':checkbox[name=dhcp]').each(
					function (i, e) {
						var $dhcp = $(e);
						
						$dhcp.off('click.IoT');
						$dhcp.on(
							'change.IoT',
							function () {
								$form.find('input[name=ip],input[name=mask]').prop('disabled', $dhcp.is(':checked'));
								if ($dhcp.is(':checked')) {
									$form.find('input[name=ip],input[name=mask]').attr('disabled', 'disabled');
								}
							}
						);
					}
				);
				
				$this.find('button[type=button]').each(
					function (i, e) {
						var $button = $(e);
						
						$button.off('click.IoT');
						$button.on(
							'click.IoT',
							function () {
								$.ajax(
									{
										url:  $form.attr('action'),
										type: 'POST',
										data: {
											action: $button.text(),
											interface: interfaceName
										}
									}
								);
								window.location.hash = '#'+interfaceName;
								window.location.reload();
							}
						);
					}
				);
			}
		);
	}
})(jQuery);
