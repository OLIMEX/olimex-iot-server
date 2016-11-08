<?php
$id = prmGET('id');
if (empty($id)) {
	throw new Exception('Missing property ID', 400);
}

$property = PropertyManager::get($id);

$title = 'Delete property';
$action = 'Delete';
$disabled = TRUE;

include('form.php');
