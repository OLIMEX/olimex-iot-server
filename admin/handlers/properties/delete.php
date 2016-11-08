<?php 
$id = prmPOST('id');
if (empty($id)) {
	throw new Exception('Missing property ID', 400);
}

$property = PropertyManager::get($id);
if (empty($property)) {
	throw new Exception('Invalid property', 400);
}

try {
	PropertyManager::delete($property);
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
