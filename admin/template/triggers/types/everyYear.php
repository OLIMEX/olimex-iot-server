<?php 

$trigger = TriggerManager::get(prmGET('id', $_SESSION['copyTrigger']));
if (empty($trigger) || $trigger->type() != 'everyYear') {
	$minutes = sessPOST('minutes', 0);
	$hour = sessPOST('hour', 0);
	$date = sessPOST('date', 1);
	$month = sessPOST('month', 'January');
} else {
	$data = $trigger->data();
	$minutes = $data->minutes;
	$hour = $data->hour;
	$date = $data->date;
	$month = $data->month;
}

?>
<div id="trigger-parameters">
	<label for="month">At</label> 
	<div class="group">
		<?php echo $this->__call('/clock/month', array($month)); ?>
		<?php echo $this->__call('/clock/date', array($date)); ?> 
		<?php echo $this->__call('/clock/hour', array($hour)); ?>
		:
		<?php echo $this->__call('/clock/minutes', array($minutes)); ?>
	</div>	
</div>
