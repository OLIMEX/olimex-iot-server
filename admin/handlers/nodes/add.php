<?php
if (!NodeManager::canAdd()) {
	throw new Exception('Access denied', 401);
}

try {
	$userID = UserManager::isAdmin() ?
		prmPOST('userID')
		:
		UserManager::id()
	;
	
	NodeManager::save(
		new Node(-1, UserManager::get($userID), prmPOST('name'), prmPOST('token'))
	);
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
