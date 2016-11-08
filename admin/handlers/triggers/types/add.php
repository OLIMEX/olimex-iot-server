<?php 
if (!UserManager::isAdmin()) {
	throw new Exception('Access denied', 401);
}

try {
	TriggerType::add(prmPOST('name'));
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
