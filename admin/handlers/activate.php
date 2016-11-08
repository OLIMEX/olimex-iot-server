<?php 
$user = UserManager::current();
try {
	if (empty($user)) {
		if (prmPOST('email') != NULL) {
			$user = UserManager::getByEMail(prmPOST('email'));
			if (empty($user)) {
				throw new Exception('Unknown email address');
			}
			
			UserManager::current($user);
			
			$activationCode = Config::user('activationCode');
			if (empty($activationCode)) {
				UserManager::current(NULL);
				throw new Exception('User is already activated');
			}
			
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
				UserManager::current(NULL);
				throw new Exception('Send mail failed');
			}
			
			Breadcrumb::reload();
			return;
		}
		throw new Exception('Invalid user');
	}
	
	$user->activate(prmPOST('activationCode'));
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
