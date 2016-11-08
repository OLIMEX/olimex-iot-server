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
	ignore_user_abort(TRUE);
	set_time_limit(0);
	
	if ($node->active()) {
		$node->post(
			'/config/about',
			array(
				'Reset' => 1
			)
		);
	}
	NodeManager::delete($node);
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
