<?php 
list(
	$id,
	$data,
	$type
) = array_pad($this->_parameters_, 3, NULL);

if (empty($id)) {
	throw new Exception('Missing trigger [id] parameter');
}

if (empty($data)) {
	throw new Exception('Missing trigger [data] parameter');
}

if (empty($type)) {
	throw new Exception('Missing trigger [type] parameter');
}

$triggerID = $data->TriggerID;
$trigger = TriggerManager::get($triggerID);
if (empty($trigger)) {
	throw new Exception('Unknown trigger ['.$triggerID.']');
}

function traceHREF(Event $event, $property) {
	return '/svg/property?'.
		'node='.$event->nodeName().'&'.
		'device='.$event->deviceName().'&'.
		'property='.$property.'&'.
		'id='.$event->id()
	;
}

?>
<div class="trigger">
	<?php if ($type == 'cause') { ?>
		<?php $because = EventsManager::get($data->ref); ?>
		<b>Reason for change</b>
		<hr/>
		<p>
			Node: <?php echo $because->nodeName(); ?><br/>
			Device: <?php echo $because->deviceName(); ?><br/>
			Properties:
			<?php if ($trigger->type() == 'onPropertyChange') { ?>
				<a href="<?php echo traceHREF($because, $trigger->data()->property); ?>">
					<?php echo $trigger->data()->property; ?>
				</a>
			<?php } else { ?>
				<?php foreach (PropertyManager::getByDevice($because->device()) as $property) { ?>
					<a href="<?php echo traceHREF($because, $property->name()); ?>">
						<?php echo $property->name(); ?>
					</a>
				<?php } ?>
			<?php } ?>
		</p>
		<hr/>
	<?php } ?>
	
	<b>Trigger fired</b>
	<p>
		<a href="/triggers/edit?id=<?php echo $trigger->id(); ?>">
		<?php echo $trigger->type(); ?><br/>
		<?php echo $trigger->dataToString(); ?>
		</a>
	</p>
	<?php echo $this->__call('/svg/action', array($data)); ?>
	
	<?php if ($type == 'fire') { ?>
		<?php $causedBy = EventsManager::causedBy($id); ?>
		<?php if (count($causedBy) > 0) { ?>
			<b>Caused changes</b>
			<hr/>
			<?php foreach ($causedBy as $caused) { ?>
				<p>
					Node: <?php echo $caused->nodeName(); ?><br/>
					Device: <?php echo $caused->deviceName(); ?><br/>
					Properties:
					<?php foreach (PropertyManager::getByDevice($caused->device()) as $property) { ?>
						<?php if (!$property->isNumeric()) continue; ?>
						<a href="<?php echo traceHREF($caused, $property->name()); ?>">
							<?php echo $property->name(); ?>
						</a>
					<?php } ?>
				</p>
				<hr/>
			<?php } ?>
		<?php } ?>
	<?php } ?>
</div>
