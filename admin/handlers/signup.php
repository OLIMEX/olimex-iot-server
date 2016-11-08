<?php 

try {
	if (prmPOST('password1') !== prmPOST('password2')) {
		throw new Exception('Password does not match!');
	}
	
	if (prmPOST('agree') == NULL) {
		throw new Exception('You have to agree with Terms and Conditions');
	}
	
	$userID = UserManager::save(
		new User(-1, prmPOST('user'), prmPOST('password1'), FALSE, prmPOST('email'))
	);
	
	$user = UserManager::get($userID);
	$activationCode = random_str(8);
	
	UserManager::current($user);
	Config::user('activationCode', $activationCode);
	Config::save();
	// UserManager::current(NULL);
	
	ob_start();
	include __DIR__.'/email/signup.php';
	$message = ob_get_clean();
	
	$sent = Mail::create()->
		setFrom(Config::system('email.from'))->
		addTo(prmPOST('email'))->
		setSubject(Config::system('service.name').' - User Activation')->
		setHTMLBody($message)->
		send()
	;
	
	if (!$sent) {
		throw new Exception('Send mail failed');
	}
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
