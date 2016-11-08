<?php
$id = prmGET('id');
if (empty($id)) {
	throw new Exception('Missing node ID', 400);
}

$node = NodeManager::get($id);

$title = 'Edit node';
$action = 'Save';
$disabled = FALSE;

include('form.php');
