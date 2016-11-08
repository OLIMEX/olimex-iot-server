<?php 
	$user = UserManager::current();
	if (!empty($user)) {
		echo $this->dashboard();
		return;
	}
?>

<script src="<?php echo $this->version('/scripts/home.js'); ?>"></script>

<h1><?php echo Config::system('service.name'); ?></h1>

<div class="home container">
	<div>
		<a href="/login" title="Login"><img src="/images/login.png" /></a><br/>
		Login
	</div>
	
	<div>
		<a href="/signup" title="Sign up"><img src="/images/signup.png" /></a><br/>
		Sign up
	</div>
	
	<div>
		<a href="/setup" title="New node setup" target="_blank"><img src="/images/setup.png" /></a><br/>
		New node setup
	</div>
	
	<div>
		<a href="/direct" title="Direct node connection" target="_blank"><img src="/images/direct.png" /></a><br/>
		Direct node connection
	</div>
</div>