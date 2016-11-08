(function ($) {
	$(document).on(
		'readyIoT',
		function () {
			$('.emtr-reset').emtrReset();
			$('.confirmation').confirmation();
		}
	);
})(jQuery);

(function ($) {
	$.fn.emtrReset = function () {
		return this.each(
			function (i, e) {
				var $this = $(e);
				var $form = $this.closest('form');
				var $counters = $form.find('input.counter');
				
				$this.
					on(
						'click',
						function () {
							$('.confirmation').trigger('confirm', $this);
							return false;
						}
					).
					
					on(
						'submit',
						function () {
							var data = {};
							$counters.each(
								function (i, e) {
									data[e.name] = 0;
								}
							);
							$form.trigger('postIoT', data);
							$('.modal').hide();
							return false;
						}
					)
				;
			}
		);
	}
})(jQuery);
