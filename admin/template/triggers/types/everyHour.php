<?php 

$trigger = TriggerManager::get(prmGET('id', $_SESSION['copyTrigger']));
if (empty($trigger) || $trigger->type() != 'everyHour') {
	$minutes = sessPOST('minutes', 0);
} else {
	$data = $trigger->data();
	$minutes = $data->minutes;
}

?>
<div id="trigger-parameters">
	<label for="minutes">At</label> <?php echo $this->__call('/clock/minutes', array($minutes)); ?> minute
</div>
