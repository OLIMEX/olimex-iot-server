(function ($) {
	$(document).on(
		'readyIoT',
		function () {
			$('.calendar').calendar();
		}
	);
})(jQuery);

(function ($) {
	$.fn.calendar = function () {
		return this.each(
			function (i, e) {
				var $this = $(e);
				
				var $form = $this.find('form');
				
				var $prev = $this.find('.prev');
				var $next = $this.find('.next');
				
				var $month = $this.find('input[name=month]');
				var $date  = $this.find('input[name=month]');
				
				$this.go = function (offset) {
					var month = new Date($month.val());
					month.setMonth(month.getMonth() + offset);
					$month.val(month.toISOString().substr(0,10));
				}
				
				$prev.
					off('.calendar').
					on(	'click.calendar',
						function (event) {
							$this.go(-1);
							event.stopPropagation();
						}
					)
				;
				
				$next.
					off('.calendar').
					on(	'click.calendar',
						function (event) {
							$this.go(1);
							event.stopPropagation();
						}
					)
				;
				
				$form.
					off('.calendar').
					on('submit.calendar',
						function (event) {
							$.ajax(
								{
									method : 'GET',
									url: $form.attr('action'),
									data: $form.serialize()
								}
							).done(
								function (data, status) {
									if (status != 'success') {
										return;
									}
									$this.replaceWith(data);
									$('.calendar').calendar().show();
								}
							);
							
							event.stopPropagation();
							event.preventDefault();
							return false;
						}
					)
				;
			}
		);
	};
})(jQuery);
