<?php 
$ids = prmGET('ids');
if (empty($ids)) {
	throw new Exception('Missing event IDs', 400);
}

$triggerEvents = EventsManager::getMany($ids);
?>

<div class="fire box">
<?php 
foreach ($triggerEvents as $event) {
	echo $this->__call('/svg/trigger', array($event->id(), $event->data()->Trigger, 'fire'));
}
?>
</div>