<?php 
$id = prmGET('id');
if (empty($id)) {
	throw new Exception('Missing trigger ID', 400);
}

$trigger = TriggerManager::get($id);
if (empty($trigger)) {
	throw new Exception('Invalid trigger', 400);
}

try {
	$trigger->active(prmGET('set', 0));
	
	TriggerManager::save($trigger);
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	Breadcrumb::back();
	return;
}

Breadcrumb::back();
