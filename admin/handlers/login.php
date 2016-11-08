<?php

if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
	$user = UserManager::login(
		prmPOST('user'),
		prmPOST('password')
	);

	if (empty($user)) {
		$_SESSION['error'] = 'Invalid user';
		Breadcrumb::reload();
		return;
	}

	Breadcrumb::back();
	return;
}

$request = json_decode(
	file_get_contents('php://input')
);
header('Content-Type: application/json');

$unauthorized = json_encode(
	array(	
		'Error' => array(
			'message' => 'Unauthorized user'
		)
	),
	JSON_FORCE_OBJECT
);

if (empty($request->User) || empty($request->Password)) {
	echo $unauthorized;
	return;
}

$user = UserManager::login(
	$request->User,
	$request->Password
);

if (empty($user)) {
	echo $unauthorized;
	return;
}

echo json_encode(
	array(
		'User' => array(
			'id'   => $user->id(),
			'name' => $user->name()
		)
	),
	JSON_FORCE_OBJECT
);
