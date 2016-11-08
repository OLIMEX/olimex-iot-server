/**
 * Drag
 */
(function ($) {
	
	var dragStart = null;
	var dragMove  = false;
	var $document = $(document);
	
	$.fn.drag = function () {
		return this.each(
			function (index, element) {
				var $this = $(element);
				
				$this.
					off('.drag').
					
					on('mousedown.drag',
						function (event) {
							event.stopPropagation();
							
							if (dragStart) {
								return;
							}
							
							dragStart = event;
							dragMove  = false;
							
							$document.
								on('mousemove.drag',
									function (event) {
										if (dragStart) {
											dragMove = true;
											event.stopImmediatePropagation();
											event.stopPropagation();
											event.preventDefault();

											event.which = dragStart.which;
											
											$this.trigger(
												$.Event(
													'drag', 
													{
														which   : dragStart.which,
														offsetX : event.pageX - dragStart.pageX,
														offsetY : event.pageY - dragStart.pageY
													}
												)
											);
											
											dragStart = event;
										}
									}
								).
								
								on('mouseup.drag',
									function (event) {
										if (dragStart && dragMove) {
											event.stopImmediatePropagation();
											event.stopPropagation();
											event.preventDefault();
											
											$this.trigger(
												$.Event(
													'drop', 
													{
														which : dragStart.which,
														pageX : event.pageX,
														pageY : event.pageY
													}
												)
											);
										}
										
										$document.off('.drag');
										dragStart = null;
									}
								)
							;
						}
					).
					
					on('mousewheel.drag DOMMouseScroll',
						function (event) {
							var delta = event.originalEvent.detail ?
								event.originalEvent.detail * 40
								:
								-event.originalEvent.wheelDelta
							;
							
							$this.trigger(
								$.Event(
									'zoom', 
									{
										pageX: event.pageX || event.originalEvent.pageX,
										pageY: event.pageY || event.originalEvent.pageY,
										zoomX: delta,
										zoomY: delta
									}
								)
							);
							
							event.stopPropagation();
							return false;
						}
					) 
				;
					
			}
		);
	};
})(jQuery);
