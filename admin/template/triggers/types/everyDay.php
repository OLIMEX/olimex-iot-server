<?php 

$trigger = TriggerManager::get(prmGET('id', $_SESSION['copyTrigger']));
if (empty($trigger) || $trigger->type() != 'everyDay') {
	$minutes = sessPOST('minutes', 0);
	$hour = sessPOST('hour', 0);
	
	$dowMask = 0;
	$dow = sessPOST('dow', array(1, 2, 4, 8, 16, 32, 64));
	if (is_array($dow)) {
		foreach ($dow as $d) {
			$dowMask += $d;
		}
	}
} else {
	$data = $trigger->data();
	$minutes = $data->minutes;
	$hour = $data->hour;
	$dowMask = $data->dowMask;
}

?>
<div id="trigger-parameters">
	<label for="hour">At</label>
	<div class="group">
	<?php echo $this->__call('/clock/hour', array($hour)); ?>
	: 
	<?php echo $this->__call('/clock/minutes', array($minutes)); ?>
	</div>
	<label>Day of week</label> <?php echo $this->__call('/clock/dow', array($dowMask)); ?>
</div>
