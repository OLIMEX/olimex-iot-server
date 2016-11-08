<?php 
$nodeName = prmGET('node');
$deviceName = prmGET('device');
$property = prmGET('property');
$date = prmGET('date');
$id = prmGET('id');

if (!empty($id)) {
	$hilight = EventsManager::get($id);
	if (!empty($hilight)) {
		$date = $hilight->timestamp()->format('Y-m-d');
	}
}

if (empty($date)) {
	$date = date('Y-m-d');
}

$node = NodeManager::getByName($nodeName);
if (empty($node)) {
	$node = NodeManager::getByToken($nodeName);
	if (empty($node)) {
		throw new Exception('Invalid node', 400);
	}
}

$device = DeviceManager::getByName($deviceName);
if (empty($device)) {
	$device = DeviceManager::getByPath($deviceName);
	if (empty($device)) {
		throw new Exception('Invalid device', 400);
	}
}

$property = PropertyManager::getByName($device->name(), $property);
if (empty($property)) {
	throw new Exception('Invalid property', 400);
}

$date = DateTime::createFromFormat('Y-m-d H:i:s', $date.' 00:00:00');
if (empty($date)) {
	throw new Exception('Invalid date', 400);
}

$data = PropertyManager::log($node, $property, $date);

$focus = NULL;
$svg = new SVG(
	$data,
	$property->type(),
	'green',
	420, 200,
	$property->measure(),
	function ($svg, $i, &$d) use ($id, &$focus, $property, $date) {
		if ($id === $d['id']) {
			$focus = $i;
		}
		
		$dt = new DateTime($d['x']);
		
		$d['label'] = 
			'<b>'.$dt->format('H:i:s').'</b>'."\n".
			$property->label().' <b>'.$d['y'].$property->measure().'</b>'
		;
		
		$d['x'] = (integer)($dt->getTimestamp() - $date->getTimestamp());
		$d['color'] = $d['triggers'] ? 'red' : ($d['causeLogID'] ? 'orange' : $svg->color());
		$d['opacity'] = $d['color'] == $svg->color() ? 0.6 : 1;
	}
);

$dateBaseURL = '/svg/property?node='.$node->name().'&device='.$device->name().'&property='.$property->name();
?>

<script src="<?php echo $this->version('/scripts/svg.js'); ?>"></script>
<script src="<?php echo $this->version('/scripts/calendar.js'); ?>"></script>

<script>
	var TIMEOUT = 5000;
	var SVG_DATA = JSON.parse(<?php var_export(json_encode($data)); ?>);
	var SVG_FOCUS_POINT = <?php echo empty($focus) ? 'null' : 'SVG_DATA['.$focus.']'; ?>;
	
	function requestDetails(request, container) {
		$.ajax(
			request
		).done(
			function (data, status) {
				if (status != 'success') {
					return;
				}
				container.append(data);
			}
		);
	}
	
	(function ($) {
		$(document).on(
			'readyIoT',
			function () {
				$('.date').
					off('.IoT').
					on('click.IoT',
						function (event) {
							$('.calendar').show();
							return false;
						}
					)
				;
				
				$('.svg').svg(
					SVG_DATA,
					{
						zoomMax: 10,
						focusData: SVG_FOCUS_POINT,
						
						dotRadius: 4,
						
						dotColor: function (focusData) {
							return (focusData.triggers ? 
								'red' 
								: 
								(focusData.causeLogID ?
									'orange'
									:
									'<?php echo $svg->color(); ?>'
								)
							);
						},
						
						filterKeys: function (data) {
							if (data.triggers) {
								return true;
							}
							
							if (data.causeLogID) {
								return true;
							}
							
							return false;
						},
						
						extraDetails: function (focusData, show) {
							$('.cause').remove();
							$('.fire').remove();
							
							if (!show) {
								return;
							}
							
							var $container = $(this.element).parent();
							var request = false;
							
							if (focusData.causeLogID) {
								request = true;
								requestDetails(
									{
										url:  '/svg/cause',
										type: 'GET',
										data: {
											id: focusData.causeLogID
										},
										timeout: TIMEOUT
									},
									$container
								);
							} 
							
							if (focusData.triggers) {
								request = true;
								requestDetails(
									{
										url:  '/svg/fire',
										type: 'GET',
										data: {
											ids: focusData.triggers.map(
												function (t) {
													return t.triggerLogID;
												}
											).join(',')
										},
										timeout: TIMEOUT
									},
									$container
								);
							}
							
							if (!request) {
								$container.append('<div class="cause">No additional information available.</div>');
							}
						}
							
					}
				)
			}
		);
	})(jQuery);
</script>

<?php echo $this->backArrow(); ?>
<h1><?php echo $node->name().'.'.$device->name(); ?></h1>

<div class="svg">
	<div class="container">
		<div class="title"><?php echo $date->format('d M Y'); ?></div>
		<svg xmlns="http://www.w3.org/2000/svg" version="1.1"  
			 width="100%" height="<?php echo ($svg->height()+15); ?>"
		>
			<?php if (!empty($data) && $property->type() != 'binary' && $property->measure() != '') { ?>
				<svg class="grid"
					viewBox="<?php echo $svg->gridViewBox(); ?>"
					preserveAspectRatio="none" 
					width="100%" height="<?php echo $svg->height(); ?>"
				>
					<path d="<?php echo $svg->yGridPath(); ?>"
						stroke="grey" 
						stroke-width="0.5" 
						vector-effect="non-scaling-stroke" 
					/>
					
					<?php foreach ($svg->yGridText(14) as $text) { ?>
						<text 
							<?php foreach ($text as $name => $value) { ?>
								<?php if ($name == 'text') continue; ?>
								<?php echo $name.'="'.$value.'"';?>
							<?php } ?>
						><?php echo $text['text'];?></text>
					<?php } ?>
				</svg>
			<?php } ?>
			
			<svg class="graph" 
				viewBox="<?php echo $svg->viewBox(); ?>" 
				preserveAspectRatio="none" 
				width="100%" height="<?php echo $svg->height(); ?>"
			>
				<defs>
					<linearGradient id="gradient-<?php echo $property->name(); ?>">
					<?php foreach ($svg->gradient() as $g) { ?>
						<stop offset="<?php echo $g['offset']; ?>%" stop-color="<?php echo $g['color']; ?>" stop-opacity="<?php echo $g['opacity']; ?>" />
					<?php } ?>
					</linearGradient>
				</defs>
				<polyline class="graph-data"
					points="<?php echo $svg->points(); ?>" 
					fill="none" 
					stroke="url(#gradient-<?php echo $property->name(); ?>) <?php echo $svg->color(); ?>"
					stroke-width="2" 
					vector-effect="non-scaling-stroke"
				/>
			</svg>
		</svg>
		<div class="dot">
			<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 12 12" width="12" height="12">
				<circle cx="6" cy="6" r="6" 
					fill="white"
					stroke="none" 
					stroke-width="2"
					stroke-opacity="0.6"
				/>
				<circle cx="6" cy="6" r="4" fill="<?php echo $svg->color(); ?>" />
			</svg>
		</div>
		<div class="label"></div>
	</div>
	
	<div class="navigation">
		
		<?php echo 
			$this->__call(
				'/calendar', 
				array(
					$date->format('Y-m-d'), 
					NULL, 
					'id'
				)
			); 
		?>
		<div class="date" title="Date Select"></div>
		<div class="shift-left" title="Move Left"></div>
		
		<div>
			<div class="zoom-in" title="Zoom IN"></div>
			<div class="reset" title="Reset"></div>
			<div class="zoom-out" title="Zoom OUT"></div>
		</div>
		
		<div class="shift-right" title="Move Right"></div>
	</div>
	
	<div class="legend">
		<h4><?php echo $property->label(); ?></h4>
		<p>
			<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 8 8" width="8" height="8">
				<circle cx="4" cy="4" r="4" fill="red" />
			</svg>
			Trigger fired
		</p>
		
		<p>
			<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 8 8" width="8" height="8">
				<circle cx="4" cy="4" r="4" fill="orange" />
			</svg>
			Reason for change
		</p>
		
		<p>
			<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 8 8" width="8" height="8">
				<circle cx="4" cy="4" r="4" fill="<?php echo $svg->color(); ?>" />
			</svg>
			Regular Data
		</p>
	</div>
</div>
