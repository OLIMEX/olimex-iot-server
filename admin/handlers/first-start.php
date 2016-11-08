<?php 
if (UserManager::countUsers() > 0) {
	throw new Exception('Access denied', 401);
}

$firstStart = TRUE;
$_SESSION['firstStart'] = TRUE;

include('users/add.php');