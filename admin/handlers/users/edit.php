<?php 
$user = UserManager::current();
$id = prmPOST('id');
if ($user->isAdmin() && !empty($id)) {
	$user = UserManager::get($id);
}

if (empty($user)) {
	throw new Exception('Invalid user', 400);
}

$newAPI = (prmPOST('action') == 'API');
$passwordChanged = FALSE;

try {
	if (
		UserManager::id() == $user->id() && 
		$user->password_verify(prmPOST('password')) == FALSE
	) {
		throw new Exception('Invalid old password');
	}
	
	if ($newAPI) {
		$user->newApiKey();
	} else {
		if (prmPOST('password1') !== prmPOST('password2')) {
			throw new Exception('New password does not match');
		}
		
		if (prmPOST('password1') != NULL) {
			$user->password(prmPOST('password1'));
			$passwordChanged = (prmPOST('password') != prmPOST('password1'));
		}
		
		if (
			UserManager::isAdmin() && 
			$user->id() != UserManager::id()
		) {
			$user->isAdmin(prmPOST('isAdmin'));
		}
		
		$user->email(prmPOST('email'));
	}
	
	UserManager::save($user);
	
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

if ($newAPI) {
	Breadcrumb::reload();
} else {
	Breadcrumb::back();
}
