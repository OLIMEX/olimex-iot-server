<?php 
$key = Cryptography::open(prmGET('key'));
if (!empty($key)) {
	try {
		$data = @json_decode($key);
		
		if (empty($data->key) || empty($data->code)) {
			throw new Exception();
		}
	} catch (Exception $e) {
		throw new Exception('Invalid password change key');
	}
	
	$user = UserManager::getByApiKey($data->key);
	if (empty($user)) {
		$_SESSION['error'] = 'Can not change password of unknown user';
		Breadcrumb::go('/');
	} else {
		UserManager::current($user);
		if (Config::user('passwordCode') != $data->code) {
			UserManager::current(NULL);
			throw new Exception('Invalid password change code');
		}
		Breadcrumb::go('/password/change');
	}
	return;
}
?>

<?php echo $this->backArrow(); ?>
<h1>Password recovery</h1>

<p class="guide">
	Enter the e-mail address associated with your <?php echo Config::system('service.name'); ?> 
	account, then click Send. We'll email you a link to a site where you can reset your password.
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
