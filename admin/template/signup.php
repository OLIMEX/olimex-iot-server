<?php 
	$user = UserManager::current();
	if (!empty($user)) {
		echo $this->dashboard();
		return;
	}
?>
<?php echo $this->backArrow(); ?>
<h1>Sign Up</h1>

<p class="guide error">All fields are mandatory</p>
<form class="container" method="POST">
	<label for="user">User</label>
	<input type="text" id="user" name="user" value="<?php echo htmlentities(sessPOST('user')); ?>"/>
	
	<label for="password1">Password</label>
	<input type="password" id="password1" name="password1"/>
	
	<label for="password2">Retype Password</label>
	<input type="password" id="password2" name="password2"/>
	
	<label for="email">e-mail</label>
	<input type="text" id="email" name="email" value="<?php echo htmlentities(sessPOST('email')); ?>"/>
	
	<label for="agree">I'm agree with Terms and Conditions below</label>
	<input type="checkbox" id="agree" name="agree" value="1"/>
	
	<button type="submit">Sign Up</button>
	<button type="button">Back</button>
</form>

<div>
	<h2>Terms and Conditions</h2>
	<p class="guide">
		Please read these Terms and Conditions carefully before using the https://iot.olimex.com service 
		operated by Olimex Ltd.
	</p>
	<p class="guide">
		Your access to and use of the Service is conditioned on your acceptance of and compliance with
		these Terms. These Terms apply to all visitors, users and others who access or use the Service.
	</p>
	<p class="guide">
		<b>By accessing or using the Service you agree to be bound by these Terms. If you disagree
		with any part of the terms then you may not access the Service.</b>
	</p>
	
	<h3>Liability</h3>
	<p class="guide">
		You further acknowledge and agree that Olimex Ltd shall not be responsible or liable, directly or 
		indirectly, for any damage or loss caused or alleged to be caused by or in connection with use of 
		or reliance on Service.		
	</p>
</div>
