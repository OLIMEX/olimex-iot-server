<?php 
try {
	PropertyManager::save(
		new Property(
			-1, 
			DeviceManager::get(prmPOST('deviceID')), 
			prmPOST('name'), 
			
			prmPOST('type'), 
			prmPOST('readOnly'), 
			prmPOST('factor'), 
			prmPOST('decimaps'), 
			
			prmPOST('label'), 
			prmPOST('measure'), 
			prmPOST('inputType'), 
			
			prmPOST('jsonPath'), 
			prmPOST('description')
		)
	);
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
