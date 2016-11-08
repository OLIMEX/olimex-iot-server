<?php 

try {
	$user = UserManager::current();
	if ($user->isAdmin()) {
		foreach(prmPOST('system') as $name => $value) {
			Config::system($name, $value);
		}
	}

	foreach(prmPOST('user') as $name => $value) {
		if ($name == 'timezone') {
			if (!in_array($value, timezone_identifiers_list())) {
				throw new Exception('Invalid time zone');
			}
		}
		Config::user($name, $value);
	}

	Config::save();
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

if (!empty($_SESSION['firstStart'])) {
	if (WiFi::check() == 0) {
		Breadcrumb::go('/');
	} else {
		Breadcrumb::go('/scan/config');
	}
	return;
}

Breadcrumb::back();
