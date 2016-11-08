<?php 
$trigger = prmGET('trigger');
$propertyID = prmGET('propertyID');
$filter = prmGET('filter');
$expression = NULL;

switch ($trigger) {
	case 'onRegisterDevice':
		$expression = '/Registered/';
		$device = '*';
	break;
	
	case 'onConnectionClose':
		$expression = '/Node/';
		$device = '*';
	break;
	
	case 'everyMinute':
	case 'everyHour':
	case 'everyDay':
	case 'everyMonth':
	case 'everyYear':
		$device = 'CLOCK';
	break;
	
	default:
		$property = PropertyManager::get($propertyID);
		if (empty($property)) {
			$device = NULL;
		} else {
			$device = $property->deviceName();
			if (!empty($filter)) {
				$expression = '/Filter/';
			}
		}
	break;
}

$properties = PropertyManager::getAll();
usort(
	$properties,
	function ($a, $b) use ($device, $expression) {
		if ($a->deviceName() === $b->deviceName()) {
			if (empty($expression)) {
				return $a->name() > $b->name() ?
					1
					:
					-1
				;
			}
			
			$expA = preg_match($expression, $a);
			$expB = preg_match($expression, $b);
			
			return ($expA == $expB ?
				($a->name() > $b->name() ?
					1
					:
					-1
				)
				:
				($expA > $expB ?
					-1
					:
					1
				)
			);
		}
		
		if ($a->deviceName() === $device) {
			return -1;
		}
		
		if ($b->deviceName() === $device) {
			return 1;
		}
		
		return $a->deviceName() > $b->deviceName() ?
			1
			:
			-1
		;
	}
);
?>

<?php foreach ($properties as $p) { ?>
	<?php if (
		$device === NULL ||
		$p->device() === NULL ||
		$device === $p->deviceName()
	) { 
		$success = ($p->device() != NULL && $device === $p->deviceName());
		$success = empty($expression) ?
			$success
			:
			$success || preg_match($expression, $p->name())
		;
	?>
		<div <?php echo ($success ? 'class="success"' : ''); ?>><?php echo $p->name(); ?></div>
	<?php } ?>
<?php } ?>
