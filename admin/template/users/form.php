<?php 
if (empty($user)) {
	throw new Exception('Invalid user', 400);
}

if (isset($_SESSION['post'])) {
	if ($user->id() < 0) {
		$user->name(sessPOST('name'), TRUE);
		$user->isAdmin(sessPOST('isAdmin'));
	}
	$user->email(sessPOST('email'), TRUE);
}

$_SESSION['post'] = NULL;
unset($_SESSION['post']);

?>

<script src="<?php echo $this->version('/scripts/users.js'); ?>"></script>

<?php echo $this->backArrow(); ?>
<h1><?php echo $title; ?></h1>
<?php if (!empty($firstStart)) { ?>
	<p>This is first start of the system. Please add admin user.</p>
<?php } ?>
<form method="POST" action="<?php echo $this->path(); ?>" class="container user">
	<?php if (UserManager::isAdmin()) { ?>
		<input type="hidden" name="id" value="<?php echo $user->id(); ?>" />
	<?php } ?>
	<label>Name</label>            <input type="text"     name="name"      value="<?php echo htmlentities($user->name()); ?>" <?php echo ($user->id() > 0 ? 'disabled="disabled"' : ''); ?>/>
	<?php if ($user->id() > 0 && UserManager::id() == $user->id()) { ?>
	<label>Old password</label>    <input type="password" name="password"  value="" />
	<?php } ?>
	<label>New password</label>    <input type="password" name="password1" value="" />
	<label>Retype password</label> <input type="password" name="password2" value="" />
	<?php if (UserManager::isAdmin()) { ?>
	<label>Administrator</label>   <input type="checkbox" name="isAdmin"   value="1" <?php echo ($user->isAdmin() ? 'checked="checked"' : ''); ?>/><br/>
	<?php } ?>
	<label>e-mail</label>           <input type="text"     name="email"     value="<?php echo htmlentities($user->email()); ?>"/>
	<?php if ($user->id() > 0) { ?>
	<label>API Key</label>         <div class="api-key"><?php echo $user->apiKey(); ?></div>
	<?php } ?>
	<button type="submit"><?php echo $action ?></button>
	<?php if ($user->id() > 0) { ?>
	<button type="submit" name="action" value="API">New API Key</button>
	<?php } ?>
	<button type="button">Back</button>
</form>

<?php if (empty($firstStart)) { ?>
<div class="confirmation">
	<p><b>Make sure all nodes are connected!</b></p>
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
<?php } ?>
