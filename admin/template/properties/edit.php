<?php
$id = prmGET('id');
if (empty($id)) {
	throw new Exception('Missing property ID', 400);
}

$property = PropertyManager::get($id);

$title = 'Edit property';
$action = 'Save';
$disabled = FALSE;

include('form.php');
