<?php 
$user = UserManager::current();
if (empty($user)) {
	throw new Exception('Invalid user', 400);
}

try {
	if (prmPOST('password1') == NULL) {
		throw new Exception('Password can not be empty');
	}
	
	if (prmPOST('password1') !== prmPOST('password2')) {
		throw new Exception('New password does not match');
	}
	
	$passwordChanged = ($user->password_verify(prmPOST('password1')) == FALSE);
	
	$user->password(prmPOST('password1'));
	UserManager::save($user);
	
	Config::user('passwordCode', NULL);
	Config::save();
	
	if ($passwordChanged) {
		NodeManager::newPassword($user, prmPOST('password'));
	}
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

Breadcrumb::go('/');
