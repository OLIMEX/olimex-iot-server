(function ($) {
	$(document).on(
		'readyIoT',
		function () {
			$('.user').user();
			$('.confirmation').confirmation();
			$('.node').node();
		}
	);
})(jQuery);

(function ($) {
	$.fn.user = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				var $old_password = $this.find('input[name=password]');
				var $new_password = $this.find('input[name=password1]');
				var $confirmation = $('.confirmation');
				
				$this.
					off('submit.IoT').
					on(
						'submit.IoT',
						function (event, user) {
							if (
								user || 
								$new_password.val() == '' || 
								$old_password.val() == $new_password.val()
							) {
								return true;
							}
							
							if ($confirmation.length) {
								$confirmation.trigger('confirm', $this);
								return false;
							}
							return true;
						}
					)
				;
			}
		);
	};
})(jQuery);

(function ($) {
	$.fn.node = function() {
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $about = $this.find('div.about');
				
				$this.find('h2.wifi').
					off('click.IoT').
					on(
						'click.IoT',
						function (event) {
							$about.toggle();
						}
					)
				;
			}
		);
	};
})(jQuery);
