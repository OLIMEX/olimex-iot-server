<?php 
if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
	throw new Exception('Not found', 404);
}

header('Content-Type: application/json');
$user = UserManager::current();
?>
<?php if (empty($user)) { ?>
{"Status": "Unauthorized"}
<?php } else { ?>
{
	"Status": "OK", 
	"User": {
		"id": <?php echo $user->id(); ?>, 
		"name": "<?php echo $user->name(); ?>"
	}
}
<?php } ?>