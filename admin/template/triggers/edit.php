<?php 
$id = prmGET('id');
if (empty($id)) {
	throw new Exception('Missing trigger ID', 400);
}

$trigger = TriggerManager::get($id);
if (empty($trigger)) {
	throw new Exception('Invalid trigger', 400);
}

$title = 'Edit trigger';
$action = 'Save';
$disabled = FALSE;

include('form.php');
