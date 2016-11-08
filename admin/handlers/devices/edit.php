<?php 
$id = prmPOST('id');
if (empty($id)) {
	throw new Exception('Missing device ID', 400);
}

$device = DeviceManager::get($id);
if (empty($device)) {
	throw new Exception('Invalid device', 400);
}

try {
	$device->name(prmPOST('name'));
	$device->native(prmPOST('native', 0));
	$device->eventsPath(prmPOST('eventsPath'));
	$device->description(prmPOST('description'));
	
	DeviceManager::save($device);
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
