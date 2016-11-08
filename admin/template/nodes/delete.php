<?php
$id = prmGET('id');
if (empty($id)) {
	throw new Exception('Missing node ID', 400);
}

$node = NodeManager::get($id);

$title = 'Delete node';
$action = 'Delete';
$disabled = TRUE;

if (!$node->active()) {
	$guide = 
		'<b>NOTE:</b> Deleting disconnected node may make it reappear later when it connects '.
		'because of all credentials stored in the node.'
	;
}

include('form.php');
