<?php 
if (!UserManager::isAdmin()) {
	throw new Exception('Access denied', 401);
}

$user = new User();

$title = 'Add new user';
$action = 'Add';

include('form.php');
