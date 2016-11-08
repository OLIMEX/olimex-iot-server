<?php 
if (!UserManager::isAdmin()) {
	throw new Exception('Access denied', 401);
}

$name = '';
if (isset($_SESSION['post'])) {
	$name = sessPOST('name');
}

$_SESSION['post'] = NULL;
unset($_SESSION['post']);
?>

<?php echo $this->backArrow(); ?>
<h1>Add trigger type</h1>
<form method="POST" action="<?php echo $this->path(); ?>" class="container">
	<label>Name</label> <input type="text" name="name" value="<?php echo htmlentities($name); ?>" />
	<button type="submit">Add</button>
	<button type="button">Back</button>
</form>
