<?php 

try {
	$user = UserManager::getByEMail(prmPOST('email'));
	if (empty($user)) {
		throw new Exception('Unknown e-mail address');
	}
	
	UserManager::current($user);
	
	if (Config::user('activationCode') != NULL) {
		$_SESSION['error'] = 'User is not activated';
		Breadcrumb::go('/activate');
		return;
	}
	
	$passwordCode = random_str(8);
	
	Config::user('passwordCode', $passwordCode);
	Config::save();
	UserManager::current(NULL);
	
	ob_start();
	include __DIR__.'/email/password.php';
	$message = ob_get_clean();
	
	$sent = Mail::create()->
		setFrom(Config::system('email.from'))->
		addTo(prmPOST('email'))->
		setSubject(Config::system('service.name').' - Password Reset')->
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

Breadcrumb::go('/');
