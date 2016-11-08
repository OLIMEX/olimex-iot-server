<?php 
if (empty($node)) { 
	throw new Exception('Invalid node', 400);
}

if (isset($_SESSION['post'])) {
	$node->name(sessPOST('name'), TRUE);
}

$_SESSION['post'] = NULL;
unset($_SESSION['post']);
?>

<?php echo $this->backArrow(); ?>
<h1><?php echo $title; ?></h1>
<?php if (!empty($guide)) { ?>
	<p class="guide"><?php echo $guide; ?></p>	
<?php } ?>
<form method="POST" action="<?php echo $this->path(); ?>" class="container">
	<input type="hidden" name="id"  value="<?php echo $node->id(); ?>" />
	<?php if ($action == 'Add' && UserManager::isAdmin()) { ?>
		<label>User</label>
		<select name="userID">
			<option value="<?php echo UserManager::id(); ?>">Me</option>
			<option value="">First who requests the token</option>
		</select>
	<?php } ?>
	<label>Name</label>  <input type="text" name="name"  value="<?php echo htmlentities($node->name()); ?>"  <?php echo (empty($disabled) ? '' : 'disabled="disabled"');?>/>
	<label>Token</label> <input type="text" name="token" value="<?php echo htmlentities($node->token()); ?>" disabled="disabled" />
	<button type="submit"><?php echo $action; ?></button>
	<button type="button">Back</button>
</form>
