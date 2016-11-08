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
	DeviceManager::delete($device);
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
