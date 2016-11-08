<?php 

$trigger = TriggerManager::get(prmGET('id', $_SESSION['copyTrigger']));
if (empty($trigger) || $trigger->type() != 'everyMonth') {
	$minutes = sessPOST('minutes', 0);
	$hour = sessPOST('hour', 0);
	$date = sessPOST('date', 0);
} else {
	$data = $trigger->data();
	$minutes = $data->minutes;
	$hour = $data->hour;
	$date = $data->date;
}

?>
<div id="trigger-parameters">
	<label for="date">At</label> 
		<div class="group">
		<?php echo $this->__call('/clock/date', array($date)); ?>
		<?php echo $this->__call('/clock/hour', array($hour)); ?> 
		: 
		<?php echo $this->__call('/clock/minutes', array($minutes)); ?><br/>	
		</div>
</div>
