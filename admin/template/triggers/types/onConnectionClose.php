<?php 
	$trigger = TriggerManager::get(prmGET('id', $_SESSION['copyTrigger']));
	if (empty($trigger) || $trigger->type() != 'onConnectionClose') {
		$nodeID = sessPOST('nodeID', -1);
	} else {
		$data = $trigger->data();
		
		$node = NodeManager::getByToken($data->node);
		$nodeID = empty($node) ? -1 : $node->id();
	}
?>
<div id="trigger-parameters">
	<label for="nodeID">Node</label>    <?php echo $this->__call('/nodes/select', array($nodeID)); ?>
</div>
