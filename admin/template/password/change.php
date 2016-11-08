<?php 
$user = UserManager::current();
if (empty($user)) {
	throw new Exception('Invalid user', 400);
}

$_SESSION['post'] = NULL;
unset($_SESSION['post']);

?>

<script src="<?php echo $this->version('/scripts/users.js'); ?>"></script>

<h1>Password change</h1>
<form method="POST" action="<?php echo $this->path(); ?>" class="container user">
	<label for="user">User</label>
	<input type="text" id="user" name="user" value="<?php echo htmlentities($user->name()); ?>" disabled="disabled"/>
	
	<label>New password</label>    <input type="password" name="password1" value="" />
	<label>Retype password</label> <input type="password" name="password2" value="" />
	
	<button type="submit">Change</button>
</form>

<div class="confirmation">
	<p>The new password will be set <b>ONLY</b> for connected nodes.</p>
	<p>
		You should set manually the new password for each disconnected node. 
		<b>Otherwise the disconnected nodes will have an outdated password and 
		will be unable to connect.</b>
	</p>
	<p>Do you want to proceed?</p>
	<button type="button">Yes</button>
	<button type="button">No</button>
</div>
