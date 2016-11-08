<?php 
define('DEVELOPMENT', TRUE);

if (!in_array($_SERVER['REQUEST_METHOD'], array('GET', 'POST'))) {
	die($_SERVER['REQUEST_METHOD'].' not supported!');
}

session_start();

try {
	require_once realpath(__DIR__.'/../admin/common.php');
	
	Template::page('/');
	Template::page('/signup');
	Template::page('/activate');
	Template::page('/password');
	Template::page('/password/change');
	
	Template::page('/login');
	Template::page('/logout');
	
	Template::page('/first-start');
	
	Template::page('/config');
	Template::page('/ssl', 'Configure SSL');
	
	if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
		Template::page('/users');
	}
	Template::page('/users/list');
	Template::page('/users/add');
	Template::page('/users/edit');
	
	Template::page('/nodes/add');
	Template::page('/nodes/edit');
	Template::page('/nodes/delete');
	
	Template::page('/devices/list');
	Template::page('/devices/add');
	Template::page('/devices/edit');
	Template::page('/devices/delete');
	Template::page('/devices/maximize');
	
	Template::page('/properties/list');
	Template::page('/properties/add');
	Template::page('/properties/edit');
	Template::page('/properties/delete');
	
	Template::page('/filters/list');
	Template::page('/actions/list');
	
	Template::page('/triggers/list');
	Template::page('/triggers/add');
	Template::page('/triggers/edit');
	Template::page('/triggers/delete');
	Template::page('/triggers/log');
	
	Template::page('/triggers/types/list');
	Template::page('/triggers/types/add');
	
	Template::page('/network');
	
	Template::page('/scan/config');
	Template::page('/scan/status');
	
	Template::page('/svg/property');
	
	$template = new Template(
		$_SERVER['REQUEST_METHOD'] == 'GET' ?
			realpath(__DIR__.'/../admin/template')
			:
			realpath(__DIR__.'/../admin/handlers')
	);
	
	Breadcrumb::free('/');
	Breadcrumb::free('/signup');
	Breadcrumb::free('/login');
	Breadcrumb::free('/activate');
	Breadcrumb::free('/password');
	
	Breadcrumb::local('/users');
	Breadcrumb::local('/email');
	
	Breadcrumb::forced('activationCode', '/activate');
	Breadcrumb::forced('passwordCode',   '/password/change');
	
	Breadcrumb::init($template);
	
	echo $template->render();
} catch (Exception $e) {
	HandleError($e);
}

session_write_close();
