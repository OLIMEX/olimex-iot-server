<?php 
	$user = UserManager::current();
	if (!empty($user)) {
		Breadcrumb::go('/logout');
		return;
	}
?>
<?php echo $this->backArrow(); ?>
<h1>Login</h1>

<form class="container" method="POST">
	<label for="user">User</label>
	<input type="text" id="user" name="user"/>
	
	<label for="password">Password</label>
	<input type="password" id="password" name="password"/>
	
	<button type="submit">Login</button>
	<button type="button">Back</button>
</form>
<p class="guide">
	<a href="/signup" class="right">Sign up</a>
	<a href="/password">Forgot your password?</a><br/>
	<a href="/activate">Re-send me activation code</a>
</p>
