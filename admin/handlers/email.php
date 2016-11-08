<?php 
header('Content-Type: application/json');
try {
	if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
		throw new Exception('Not found', 404);
	}
	
	$request = json_decode(
		file_get_contents('php://input')
	);
	
	if (empty($request->userID)) {
		throw new Exception('Invalid user', 400);
	}
	
	if (empty($request->Subject)) {
		throw new Exception('Missing e-mail subject', 400);
	}
	
	if (empty($request->Body)) {
		throw new Exception('Missing e-mail body', 400);
	}
	
	$user = UserManager::get($request->userID);
	if (empty($user)) {
		throw new Exception('Invalid user', 400);
	}
	
	$sent = Mail::create()->
		setFrom(Config::system('email.from'))->
		addTo($user->email())->
		setSubject(Config::system('service.name').' - '.$request->Subject)->
		setHTMLBody($request->Body)->
		send()
	;
	
	if (!$sent) {
		throw new Exception('Send mail failed');
	}
	
	$response = array(
		'Status'  => 'OK',
		'To'      => $user->email(),
		'Subject' => $request->Subject
	);
	
} catch (Exception $e) {
	$response = array(
		'Status' => 'Error',
		'Error'  => $e->getMessage()
	);
}

echo json_encode($response, JSON_FORCE_OBJECT);
