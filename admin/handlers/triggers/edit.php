<?php 
$id = prmPOST('id');
if (empty($id)) {
	throw new Exception('Missing trigger ID', 400);
}

$trigger = TriggerManager::get($id);
if (empty($trigger)) {
	throw new Exception('Invalid trigger', 400);
}

try {
	$trigger->active(prmPOST('active', 0));
	
	$trigger->user(
		UserManager::get(prmPOST('userID'))
	);
	
	$trigger->data(
		call_user_func(array($trigger->type(), 'post')),
		TRUE
	);
	
	$trigger->action(prmPOST('action'), TRUE);
	
	$trigger->clearParameters();
	
	$filter_params = prmPOST('filter_params');
	if (is_array($filter_params)) {
		foreach ($filter_params as $name => $value) {
			$trigger->addParameter(
				new TriggerParameter(
					-1,
					$trigger,
					'filter',
					$name,
					$value
				)
			);
		}
	}
	
	$action_params = prmPOST('action_params');
	if (is_array($action_params)) {
		foreach ($action_params as $name => $value) {
			$trigger->addParameter(
				new TriggerParameter(
					-1,
					$trigger,
					'action',
					$name,
					$value
				)
			);
		}
	}
	
	TriggerManager::save($trigger);
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
