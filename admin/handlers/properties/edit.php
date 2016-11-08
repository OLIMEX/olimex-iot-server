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
	$property->deviceID(prmPOST('deviceID'));
	$property->name(prmPOST('name'), TRUE);
	
	$property->type(prmPOST('type'), TRUE);
	$property->readOnly(prmPOST('readOnly'), TRUE);
	$property->factor(prmPOST('factor'), TRUE);
	$property->decimals(prmPOST('decimals'), TRUE);
	
	$property->label(prmPOST('label'), TRUE);
	$property->measure(prmPOST('measure'), TRUE);
	$property->inputType(prmPOST('inputType'), TRUE);
	
	$property->jsonPath(prmPOST('jsonPath'), TRUE);
	$property->description(prmPOST('description'), TRUE);
	
	PropertyManager::save($property);
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

Breadcrumb::back();
