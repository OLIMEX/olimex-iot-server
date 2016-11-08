<?php
$id = prmGET('id');
if (empty($id)) {
	throw new Exception('Missing device ID', 400);
}

$device = DeviceManager::get($id);

$title = 'Delete device';
$action = 'Delete';
$disabled = TRUE;

include('form.php');
