<?php 
	$trigger = TriggerManager::get(prmGET('id', $_SESSION['copyTrigger']));
	if (empty($trigger) || $trigger->type() != 'onRegisterDevice') {
		$nodeID = sessPOST('nodeID', -1);
		$deviceID = sessPOST('deviceID', -1);
	} else {
		$data = $trigger->data();
		
		$node = NodeManager::getByToken($data->node);
		$nodeID = empty($node) ? -1 : $node->id();
		
		$device = DeviceManager::getByPath($data->deviceURL);
		$deviceID = empty($device) ? -1 : $device->id();
	}
?>
<div id="trigger-parameters">
	<label for="nodeID">Node</label>    <?php echo $this->__call('/nodes/select', array($nodeID)); ?>
	<label for="deviceID">Device</label>  <?php echo $this->__call('/devices/select', array($deviceID, $nodeID, FALSE)); ?>
</div>
