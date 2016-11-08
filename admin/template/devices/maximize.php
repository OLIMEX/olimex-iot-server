<?php 
$nodeName = prmGET('node');
$deviceName = prmGET('device');

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

$off = $node->isWorking($device) ? '' : ' off';
$maximizeTemplate = __DIR__.$device->eventsPath().'/maximize.php';

$dependencies = array(
	'MOD-EMTR' => array(
		'RELAY',
		'ESP-SWITCH1',
		'ESP-SWITCH2',
	),
);

?>

<script src="<?php echo $this->version('/scripts/dashboard.js'); ?>"></script>
<script>
(function ($) {
	var done = false;
	$(document).on(
		'readyIoT',
		function () {
			if (done) {
				return;
			}
			done = true;
			$.iotEventsListen('<?php echo 'ws'.(empty($_SERVER['HTTPS']) ? '' : 's').'://'.$_SERVER['HTTP_HOST'].'/clients'; ?>');
		}
	);
})(jQuery);
</script>

<?php echo $this->backArrow(); ?>
<h1><?php echo $node->name(); ?></h1>

<div class="container">
	<?php 
	foreach ($node->devices() as $dependOn) { 
		if (
			empty($dependencies[$device->name()]) ||
			!in_array($dependOn->name(), $dependencies[$device->name()])
		) { 
			continue;
		}
		try {
			echo $this->__call('/devices/dashboard', array($node, $dependOn)); 
		} catch (Exception $e) {
			if (DEVELOPMENT) {
				echo '<div class="formIoT">'.$e->getMessage().'</div>';
			}
		}
	} 
	?>

	<div class="device <?php echo $device->name(); ?><?php echo $off; ?>">
		<h3>
			<?php echo $device->name(); ?> 
			<span class="error"></span>
		</h3>
		<form action="<?php echo $node->token().$device->eventsPath(); ?>" class="formIoT eventIoT<?php echo $off?>">
			<?php 
				if (file_exists($maximizeTemplate)) {
					include $maximizeTemplate; 
				} else {
					echo 'Maximize template  not found';
				}
			?>
		</form>
	</div>
</div>

