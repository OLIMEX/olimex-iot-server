<?php 
	$trigger = TriggerManager::get(prmGET('id', $_SESSION['copyTrigger']));
	if (empty($trigger) || $trigger->type() != 'raw') {
		$data = sessPOST('data', '');
	} else {
		$data = $trigger->data();
	}
?>
<div id="trigger-parameters">
	<label for="json-path">JSON Path</label> <input name="data" id="json-path" type="text" value="<?php echo htmlentities($data); ?>" class="required" />
</div>
