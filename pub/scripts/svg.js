(function ($) {
	$.fn.extend({
		viewBox: function (vb) {
			if (typeof vb == 'undefined') {
				vb = this[0].getAttribute('viewBox');
				if (typeof vb == 'string') {
					return vb.split(' ').map(
						function (e) {
							return Number(e);
						}
					);
				}
				return [0,0, this.outerWidth(), this.outerHeight()];
			}
			
			this[0].setAttribute('viewBox', vb.join(' '));
		}
	});
	
	function initOptions(options) {
		if (typeof options.zoomMax == 'undefined') {
			options.zoomMax = 1;
		}
		
		if (typeof options.dotColor != 'function') {
			options.dotColor = function (focusData) {
				return 'red';
			};
		}
		
		if (typeof options.extraDetails != 'function') {
			options.extraDetails = function (focusData, show) {
			};
		}
		
		if (typeof options.labelOffset == 'undefined') {
			options.labelOffset = 20;
		}
		
		if (typeof options.dotRadius == 'undefined') {
			options.dotRadius = 4;
		}
		
		if (typeof options.shiftOffset == 'undefined') {
			options.shiftOffset = 40;
		}
	}
	
	$.fn.svg = function (DATA, options) {
		return this.each(
			function (i, e) {
				if (DATA.length == 0) {
					return;
				}
				
				options.element = e;
				initOptions(options);
				
				var $svg = $(e);
				var $container = $svg.find('.container');
				var $graph = $svg.find('.graph');
				var $label = $svg.find('.label');
				var $dot = $svg.find('.dot');
				
				var $shiftLeft = $svg.find('.shift-left');
				var $shiftRight = $svg.find('.shift-right');
				var $zoomIn = $svg.find('.zoom-in');
				var $zoomOut = $svg.find('.zoom-out');
				var $reset = $svg.find('.reset');
				
				$graph.limit = $graph.viewBox();
				$graph.box = $graph.parent()[0].getBoundingClientRect();
				
				$svg.drag();
				$svg.swipe();
				
				options.dotWidth  = options.dotRadius * 2 + 8;
				options.dotCenter = options.dotWidth / 2;
				
				$dot.find('svg').
					attr('width',  options.dotWidth).
					attr('height', options.dotWidth).
					viewBox([0, 0, options.dotWidth, options.dotWidth])
				;
				$dot.find('circle:not([stroke])').
					attr('cx', options.dotCenter).
					attr('cy', options.dotCenter).
					attr('r', options.dotRadius)
				;
				$dot.find('circle[stroke]').
					attr('cx', options.dotCenter).
					attr('cy', options.dotCenter).
					attr('r',  options.dotRadius + 3)
				;
				
				var followCursor = true;
				var drag = false;
				var dragFC = null;
				var focusData = null;
				
				/* Coordinate transformations */
				
				$graph.local = function (screen) {
					var point = $graph[0].createSVGPoint();
					point.x = screen.x - $(document.body).scrollLeft();
					point.y = screen.y - $(document.body).scrollTop();
					
					return point.matrixTransform($graph[0].getScreenCTM().inverse());
				};
				
				$graph.screen = function (local) {
					var point = $graph[0].createSVGPoint();
					point.x = local.x;
					point.y = local.y;
					
					var screen = point.matrixTransform($graph[0].getScreenCTM());
					screen.x += $(document.body).scrollLeft();
					screen.y += $(document.body).scrollTop();
					return screen;
				};
				
				$graph.localLen = function (screen) {
					var point1 = $graph.local({x:0, y:0});
					var point2 = $graph.local(screen);
					return {
						x: point1.x - point2.x,
						y: point1.y - point2.y
					};
				};
				
				$graph.validateTransform = function() {
					var transform = false;
					var vb = $graph.viewBox();
					
					if (vb[0] + vb[2] > $graph.limit[0] + $graph.limit[2]) {
						vb[0] = $graph.limit[0] + $graph.limit[2] - vb[2];
						transform = true;
					}
					
					if (vb[0] < $graph.limit[0]) {
						vb[0] = $graph.limit[0];
						transform = true;
					}
					
					if (transform) {
						$graph.viewBox(vb);
					}
				};
				
				/* Labels visualization */
				
				$graph.showLabel = function () {
					if (focusData === null) {
						return;
					}
					
					$dot.show();
					var dotScreen = $graph.screen({x: focusData.x, y: focusData.y});
					$dot.offset({
						left: Math.round(dotScreen.x - options.dotCenter), 
						top:  Math.round(dotScreen.y - options.dotCenter)
					});
					
					if (focusData.label) {
						$label.html(focusData.label.replace(/[\n]/g, '<br/>'));
						$label.show();
						$label.offset({
							left: Math.round(
								dotScreen.x > Math.round($graph.box.left + $graph.box.width / 2) ?
									dotScreen.x - $label.outerWidth() - options.labelOffset
									:
									dotScreen.x + options.labelOffset
							), 
							top: Math.round(
								dotScreen.y > Math.round($graph.box.top + $graph.box.height / 2) ?
									dotScreen.y - $label.outerHeight() - options.labelOffset
									:
									dotScreen.y + options.labelOffset
							)
						});
					}
					
					$dot.find('circle:not([stroke])').attr(
						'fill', 
						options.dotColor(focusData)
					);
					$dot.find('circle[stroke]').attr(
						'stroke', 
						followCursor ?
							'none'
							:
							options.dotColor(focusData)
					).attr(
						'opacity', 
						followCursor ?
							'0.5'
							:
							'1'
					);
					
					return dotScreen;
				};
				
				$graph.focus = function (event) {
					var cursor = $graph.local({
						x: event.pageX,
						y: event.pageY
					});
					
					focusData = (typeof dragFC == 'boolean' && !dragFC) ?
						focusData
						:
						DATA.reduce(
							function (p, c) {
								return (Math.abs(cursor.x - p.x) > Math.abs(cursor.x - c.x) ?
									c
									:
									p
								);
							},
							DATA[0]
						)
					;
					
					$graph.showLabel();
				};
				
				/* Navigation */
				
				$graph.shift = function (offset) {
					offset = $graph.localLen(offset);
					
					var vb = $graph.viewBox();
					vb[0] = Math.round(vb[0] + offset.x);
					$graph.viewBox(vb);
					
					$graph.validateTransform();
					$graph.showLabel();
				};
				
				$graph.zoom = function (delta, cursor) {
					var center = false;
					if (!followCursor) {
						var dotScreen = $graph.showLabel();
						if (dotScreen && typeof dotScreen.x != 'undefined') {
							cursor = dotScreen;
							center = true;
						}
					}
					
					var w = $graph.box.width;
					var scaleX = w / (w + delta);
					
					var vb = $graph.viewBox();
					vb[2] = Math.round(vb[2] * scaleX);
					if (vb[2] < w / options.zoomMax) {
						vb[2] = w / options.zoomMax;
					}
					if (vb[2] > $graph.limit[2]) {
						vb[2] = $graph.limit[2];
					}
					
					var b = $graph.local(cursor);
					$graph.viewBox(vb);
					var a = $graph.local(cursor);
					
					vb[0] = Math.round(vb[0] + b.x - a.x);
					
					$graph.viewBox(vb);
					$graph.validateTransform();
					$graph.showLabel();
					
					if (center) {
						$graph.centerFocus();
					}
				};
				
				$graph.centerFocus = function () {
					var vb = $graph.viewBox();
					vb[0] = Math.round(focusData.x - vb[2] / 2);
					
					$graph.viewBox(vb);
					$graph.validateTransform();
					
					$graph.showLabel();
				};
				
				$graph.moveFocus = function (offset, keys) {
					if (keys) {
						followCursor = false;
						if (!focusData) {
							focusData = offset < 0 ? 
								DATA[DATA.length-1]
								:
								DATA[0]
							;
						}
					}
					
					var i = DATA.indexOf(focusData);
					if (i < 0 || offset == 0) {
						return;
					}
					
					var b = i;
					do {
						i += offset;
						if (i < 0) {
							i = 0;
							keys = true;
							break;
						}
						if (i > DATA.length-1) {
							i = DATA.length-1;
							keys = true;
							break;
						}
					} while (
						(
							!keys && 
							DATA[i].y == focusData.y
						)
						||
						(
							keys &&
							typeof options.filterKeys == 'function' && 
							!options.filterKeys(DATA[i])
						)
					);
					
					if (!keys) {
						if (i - offset != b) {
							i -= offset;
						}
					}
					
					focusData = DATA[i];
					$graph.centerFocus();
					options.extraDetails(focusData, !followCursor);
				};
				
				$graph.resetNavigation = function () {
					$graph.viewBox($graph.limit);
					$graph.showLabel();
				};
				
				/* Navigation Handlers */
				
				$shiftLeft.
					off('.SVG').
					on('click.SVG',
						function (event) {
							if (followCursor && !event.ctrlKey) {
								$graph.shift({x: options.shiftOffset, y: 0});
							} else {
								$graph.moveFocus(-1, event.ctrlKey);
							}
							event.stopPropagation();
							return false;
						}
					)
				;
				
				$shiftRight.
					off('.SVG').
					on('click.SVG',
						function (event) {
							if (followCursor && !event.ctrlKey) {
								$graph.shift({x: -options.shiftOffset, y: 0});
							} else {
								$graph.moveFocus(1, event.ctrlKey);
							}
							event.stopPropagation();
							return false;
						}
					)
				;
				
				$zoomIn.
					off('.SVG').
					on('click.SVG',
						function (event) {
							$graph.zoom(
								options.shiftOffset,
								{
									x: Math.round(($graph.box.left + $graph.box.right) / 2),
									y: Math.round(($graph.box.top + $graph.box.bottom) / 2)
								}
							);
							event.stopPropagation();
							return false;
						}
					)
				;
				
				$zoomOut.
					off('.SVG').
					on('click.SVG',
						function (event) {
							$graph.zoom(
								-options.shiftOffset,
								{
									x: Math.round(($graph.box.left + $graph.box.right) / 2),
									y: Math.round(($graph.box.top + $graph.box.bottom) / 2)
								}
							);
							event.stopPropagation();
							return false;
						}
					)
				;
				
				$reset.
					off('.SVG').
					on('click.SVG',
						function (event) {
							$graph.resetNavigation();
							event.stopPropagation();
							return false;
						}
					)
				;
				
				$svg.
					off('.SVG').
					
					on(
						'drag.SVG swipe.SVG',
						function (event) {
							if (typeof dragFC != 'boolean') {
								dragFC = followCursor;
							}
							followCursor = true;
							
							if (event.offsetX) {
								$graph.shift({
									x: event.offsetX,
									y: event.offsetY
								});
							}
						}
					).
					
					on(
						'drop.SVG',
						function (event) {
							drag = true;
							followCursor = dragFC;
							dragFC = null;
						}
					).
					
					on(
						'zoom.SVG',
						function (event) {
							$graph.zoom(
								event.zoomX + event.zoomY, 
								{
									x: event.pageX, 
									y: event.pageY
								}
							);
						}
					)
				;
					
				$container.
					off('.SVG').
					
					on(
						'contextmenu.SVG',
						function (event) {
							$graph.resetNavigation();
							return false;
						}
					).
					
					on(
						'mouseleave.SVG',
						function (event) {
							if (followCursor) {
								$label.hide();
								$dot.hide();
							}
						}
					).
					
					on(
						'mousedown.SVG',
						function(event) {
							switch (event.which) {
								case 3 : // right
									$graph.resetNavigation();
								break;
							}
						}
					).
					
					on(
						'touchstart',
						function(event) {
							followCursor = true;
						}
					).
					
					on(
						'click.SVG',
						function (event) {
							if (drag) {
								drag = false;
								return;
							}
							
							followCursor = !followCursor;
							
							if (focusData) {
								options.extraDetails(focusData, !followCursor);
							}
							
							$graph.showLabel();
						}
					).
					
					on(
						'mousemove.SVG',
						function (event) {
							drag = false;
							if (followCursor) {
								$graph.focus(event);
								return;
							}
							
							$graph.showLabel();
						}
					)
				;
				
				if (options.focusData) {
					focusData = options.focusData;
					followCursor = false;
					options.extraDetails(focusData, true);
					$graph.showLabel();
				}
			}
		);
	};
})(jQuery);
