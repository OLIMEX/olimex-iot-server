<?php 
$id = prmPOST('id');
if (empty($id)) {
	throw new Exception('Missing node ID', 400);
}

$node = NodeManager::get($id);
if (empty($node)) {
	throw new Exception('Invalid node', 400);
}

try {
	$node->name(prmPOST('name'));
	NodeManager::save($node);
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
