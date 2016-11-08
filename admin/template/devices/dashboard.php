<?php 
if (empty($this->_parameters_)) {
	return;
}

list(
	$node,
	$device
) = array_pad($this->_parameters_, 2, NULL);

if (empty($node)) {
	throw new Exception('Invalid node', 400);
}

if (empty($device)) {
	throw new Exception('Invalid device', 400);
}

$off = $node->isWorking($device) ? '' : ' off';
$maximize = file_exists(__DIR__.$device->eventsPath().'/maximize.php');
$deviceTemplate = __DIR__.$device->eventsPath().'/dashboard.php';

?>

<div class="device <?php echo $device->name(); ?><?php echo $off; ?>">
	<h3>
		<?php echo $device->name(); ?> 
		<span class="error"></span>
		<?php if ($maximize) { ?>
			<a href="/devices/maximize?node=<?php echo $node->name(); ?>&device=<?php echo $device->name(); ?>" class="maximize"></a>
		<?php } ?>
	</h3>
	<form action="<?php echo $node->token().$device->eventsPath(); ?>" class="formIoT eventIoT<?php echo $off?>">
		<?php 
			if (file_exists($deviceTemplate)) {
				include $deviceTemplate; 
			} else {
				echo 'Dashboard template not found';
			}
		?>
	</form>
</div>
