<?php
$id = prmGET('id');
if (empty($id)) {
	throw new Exception('Missing device ID', 400);
}

$device = DeviceManager::get($id);

$title = 'Edit device';
$action = 'Save';
$disabled = FALSE;

include('form.php');
