<?php 
$id = prmPOST('id');
if (empty($id)) {
	throw new Exception('Missing trigger ID', 400);
}

$trigger = TriggerManager::get($id);
if (empty($trigger)) {
	throw new Exception('Invalid trigger', 400);
}

try {
	TriggerManager::delete($trigger);
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
