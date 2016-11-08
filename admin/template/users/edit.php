<?php
$user = UserManager::current();
$id = prmGET('id');

$showNodes = FALSE;
if ($user->isAdmin() && !empty($id) && $user->id() != $id) {
	$showNodes = TRUE;
	$user = UserManager::get($id);
}

$title = 'Edit profile';
$action = 'Save';

include('form.php');

if ($showNodes) {
	include('nodes.php');
}
