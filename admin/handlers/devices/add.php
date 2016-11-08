<?php 
try {
	DeviceManager::save(
		new Device(-1, prmPOST('name'), prmPOST('native', 0), prmPOST('eventsPath'), prmPOST('description'))
	);
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
