<?php 
$id = prmGET('id');
if (empty($id)) {
	throw new Exception('Missing event ID', 400);
}

$cause = EventsManager::get($id);
if (empty($cause)) {
	throw new Exception('Invalid event', 400);
}

?>
<div class="cause box">
<?php if (isset($cause->data()->Action)) { ?>
	<b>Action</b> invoked by <?php echo $cause->data()->Action->Request->IP; ?>
	<?php echo $this->__call('/svg/action', array($cause->data()->Action)); ?>
<?php } else if (isset($cause->data()->Command)) { ?>
	<b>Dashboard command</b>
	<?php echo $this->__call('/svg/command', array($cause->data()->Command)); ?>
<?php } else if (isset($cause->data()->Trigger)) { ?>
	<?php echo $this->__call('/svg/trigger', array($cause->id(), $cause->data()->Trigger, 'cause')); ?>
<?php } else { ?>
	Unrecognised event data
<?php } ?>
</div>
