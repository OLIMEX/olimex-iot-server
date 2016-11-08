<?php 

$user = UserManager::current();

if (empty($user)) {
	if (prmGET('key') != NULL) {
		try {
			$key = Cryptography::open(prmGET('key'));
			$data = @json_decode($key);
			
			if (empty($data->key) || empty($data->code)) {
				throw new Exception();
			}
		} catch (Exception $e) {
			throw new Exception('Invalid password change key');
		}
		
		$user = UserManager::getByApiKey($data->key);
		
		if (empty($user)) {
			$_SESSION['error'] = 'Can not activate unknown user';
			Breadcrumb::go('/');
			return;
		}
		
		try {
			UserManager::current($user);
			$user->activate($data->code);
			$_SESSION['success'] = TRUE;
			Breadcrumb::go('/');
		} catch (Exception $e) {
			$_SESSION['error'] = $e->getMessage();
			Breadcrumb::reload();
		}
		return;
	}
} else {
	if (Config::user('activationCode') == NULL) {
		$_SESSION['error'] = 'Your account is already activated';
		Breadcrumb::go('/');
		return;
	}
}

$activationCode = sessPOST('activationCode');
?>

<h1>Activation</h1>
<?php if (empty($user)) { ?>
	<p class="guide">
		Enter the e-mail address associated with your <?php echo Config::system('service.name'); ?> 
		account, then click Send. We'll email your activation code again.
	</p>
	
	<p class="guide">
		It may take a few minutes to receive the e-mail notification. Do not forget to check in your Spam folder as well.
	</p>
	
	<form class="container" method="POST">
		<label for="email">e-mail</label>
		<input type="text" id="email" name="email"/>
		
		<button type="submit">Send</button>
		<button type="button">Back</button>
	</form>
<?php } else { ?>
	<p class="guide">
		Your account is not yet activated. Please activate it by entering your Activation Code.
		The Activation Code was sent to your e-mail <b><?php echo $user->email(); ?></b> during Sign Up.
	</p>

	<p class="guide">
		It may take a few minutes to receive the e-mail notification. Do not forget to check in your Spam folder as well.
	</p>

	<form class="container" method="POST">
		<label for="user">User</label>
		<input type="text" id="user" name="user" value="<?php echo htmlentities($user->name()); ?>" disabled="disabled"/>
		
		<label for="activationCode">Activation Code</label>
		<input type="text" id="activationCode" name="activationCode" value="<?php echo htmlentities($activationCode); ?>"/>
		
		<button type="submit">Activate</button>
		<button type="button">Logout</button>
	</form>
<?php } ?>
