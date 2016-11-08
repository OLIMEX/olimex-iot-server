<?php 
if (!NodeManager::canAdd()) {
	throw new Exception('Access denied', 401);
}

$node = new Node();

$title = 'Add new node';
$action = 'Add';
$disabled = FALSE;

include('form.php');
