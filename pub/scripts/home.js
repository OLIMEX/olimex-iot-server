(function ($) {
	$(document).on(
		'readyIoT',
		function () {
			$('.home div').choise();
		}
	);
})(jQuery);

(function ($) {
	$.fn.choise = function () {
		return this.each(
			function (i, e) {
				var $this = $(e);
				var url = $this.find('a').attr('href');
				
				$this.on(
					'click.IoT',
					function (event) {
						window.location = url;
						event.stopPropagation();
						return false;
					}
				);
			}
		);
	}
})(jQuery);
