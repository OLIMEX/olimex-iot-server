<?php 
if (!UserManager::isAdmin()) {
	throw new Exception('Access denied', 401);
}
if (!Network::getServerInterface()->isUSB()) {
	throw new Exception('You should use USB network interface', 401);
}
?>
<?php echo $this->backArrow(); ?>
<h1>SSL Certificate</h1>
<form method="POST" action="<?php echo $this->path(); ?>" class="container ssl">
	<label>Private key</label> <textarea name="key"></textarea>
	<label>Certificate</label> <textarea name="certificate"></textarea>
	<label>CA Chain</label>    <textarea name="chain"></textarea>
	
	<button type="submit">Install</button>
	<?php if (empty($_SESSION['firstStart'])) { ?>
	<button type="button">Back</button>
	<?php } ?>
</form>
<?php if (!empty($_SESSION['firstStart'])) { ?>
<form method="GET" action="/network">
	<button type="submit">Continue</button>
</form>
<?php } ?>
