<?php 
if (UserManager::countUsers() > 0) {
	throw new Exception('Access denied', 401);
}

$user = new User();

$title = 'Add admin user';
$action = 'Add';

$firstStart = TRUE;
include('users/form.php');
