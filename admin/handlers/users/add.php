<?php 
if (!UserManager::isAdmin() && empty($firstStart)) {
	throw new Exception('Access denied', 401);
}

try {
	if (prmPOST('password1') !== prmPOST('password2')) {
		throw new Exception('Password does not match!');
	}
	
	UserManager::save(
		new User(-1, prmPOST('name'), prmPOST('password1'), prmPOST('isAdmin') || !empty($firstStart), prmPOST('email'))
	);
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

if (!empty($firstStart)) {
	UserManager::login(prmPOST('name'), prmPOST('password1'));
	Breadcrumb::go('/ssl');
	return;
}

Breadcrumb::back();
