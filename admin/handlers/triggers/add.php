<?php 
try {
	$type = prmPOST('type');
	if (empty($type)) {
		throw new Exception('Trigger type can not be empty');
	}
	
	$trigger = new Trigger(
		-1, 
		UserManager::get(prmPOST('userID')), 
		prmPOST('active', 0), 
		$type, 
		call_user_func(array($type, 'post')), 
		prmPOST('action')
	);
	
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
