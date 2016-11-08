/**
 * Swipe
 */
(function ($) {
	
	var swipeStart = null;
	var zoomStart = null;
	var swipeMove = false;
	var $document = $(document);
	
	function getSwipeEvent(event, i) {
		if (typeof i == 'undefined') {
			i = 0;
		}
		
		if (event.originalEvent.type == 'touchend') {
			if (typeof event.originalEvent.changedTouches[i] != 'undefined') {
				return event.originalEvent.changedTouches[i];
			}
			return null;
		}
		
		if (typeof event.originalEvent.touches[i] != 'undefined') {
			return event.originalEvent.touches[i];
		}
		return null;
	}
	
	$.fn.swipe = function () {
		return this.each(
			function (index, element) {
				var $this = $(element);
				
				$this.
					off('.swipe').
					
					on('touchstart .swipe',
						function (event) {
							event.stopPropagation();
							
							if (swipeStart) {
								if (zoomStart) {
									return;
								}
								zoomStart = getSwipeEvent(event, 1);
								return;
							}
							
							swipeStart = getSwipeEvent(event);
							zoomStart = null;
							swipeMove = false;
							
							$document.
								on('touchmove .swipe',
									function (event) {
										if (swipeStart) {
											event.stopImmediatePropagation();
											event.stopPropagation();
											event.preventDefault();

											var swipeEvent = getSwipeEvent(event);
											
											if (zoomStart) {
												var zoomEvent = getSwipeEvent(event, 1);
												
												$this.trigger(
													$.Event(
														'zoom', 
														{
															touch: true,
															pageX: Math.round((swipeEvent.pageX + zoomEvent.pageX) / 2),
															
															pageY: Math.round((swipeEvent.pageY + zoomEvent.pageY) / 2),
															
															zoomX : 
																Math.abs(swipeEvent.pageX - zoomEvent.pageX) -
																Math.abs(swipeStart.pageX - zoomStart.pageX)
															,
															
															zoomY : 
																Math.abs(swipeEvent.pageY - zoomEvent.pageY) -
																Math.abs(swipeStart.pageY - zoomStart.pageY)
														}
													)
												);
												
												zoomStart = zoomEvent;
											} else {
												$this.trigger(
													$.Event(
														'swipe', 
														{
															touch: true,
															offsetX : swipeEvent.pageX - swipeStart.pageX,
															offsetY : swipeEvent.pageY - swipeStart.pageY
														}
													)
												);
											}
											
											swipeStart = swipeEvent;
											swipeMove = true;
										}
									}
								).
								
								on('touchend .swipe',
									function (event) {
										if (swipeStart && !zoomStart) {
											var swipeEvent = getSwipeEvent(event);
											if (swipeEvent) {
												if (swipeMove) {
													event.stopImmediatePropagation();
													event.stopPropagation();
													event.preventDefault();
													
													$this.trigger(
														$.Event('drop', swipeEvent)
													);
												}
											}
										}
										
										$document.off('.swipe');
										swipeStart = null;
										zoomStart = null;
									}
								)
							;
						}
					)
				;
					
			}
		);
	};
})(jQuery);
