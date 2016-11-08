<?php 
if (!UserManager::isAdmin()) {
	throw new Exception('Access denied', 401);
}
if (!Network::getServerInterface()->isUSB()) {
	throw new Exception('You should use USB network interface', 401);
}

try {
	$privateKey = prmPOST('key');
	if (empty($privateKey)) {
		throw new Exception('Private Key can not be empty');
	}
	$hPrivate = openssl_pkey_get_private($privateKey);
	if (empty($hPrivate)) {
		throw new Exception('Invalid Private Key');
	}
	
	$certificate = prmPOST('certificate');
	if (empty($certificate)) {
		throw new Exception('Certificate can not be empty');
	}
	$hPublic = openssl_pkey_get_public($certificate);
	if (empty($hPublic)) {
		throw new Exception('Invalid Certificate');
	}
	
	$caChain = prmPOST('chain');
	if (empty($caChain)) {
		throw new Exception('CA Chain can not be empty');
	}
	$hChain = openssl_pkey_get_public($caChain);
	if (empty($hChain)) {
		throw new Exception('Invalid CA Chain');
	}
	
	if (!openssl_x509_check_private_key($certificate, $privateKey)) {
		throw new Exception('Private Key and Certificate does not match');
	}
	
	$certificateNames = array();
	$certificateData = openssl_x509_parse($certificate);
	if (!empty($certificateData['subject']['CN'])) {
		$certificateNames = array($certificateData['subject']['CN']);
	}
	if (!empty($certificateData['extensions']['subjectAltName'])) {
		if (preg_match_all('/DNS:([^,]+)/', $certificateData['extensions']['subjectAltName'], $matches)) {
			$certificateNames = array_merge($certificateNames, $matches[1]);
		}
	}
	$certificateNames = array_unique($certificateNames);
	if (!in_array(Config::system('server'), $certificateNames)) {
		throw new Exception('Certificate names ['.join(', ', $certificateNames).'] does not match server name ['.Config::system('server').']');
	}
	
	$files = array(
		Config::system('data').'/'.Config::system('server').'.key' => $privateKey,
		Config::system('data').'/'.Config::system('server').'.pem' => $certificate."\n".$caChain
	);
	
	foreach ($files as $name => $data) {
		if (file_exists($name)) {
			unlink($name);
		}
		
		file_put_contents($name, $data);
		if (preg_match('/\.key$/', $name)) {
			chmod($name, 0600);
		} else {
			chmod($name, 0644);
		}
	}
	
	Config::system('ssl', TRUE);
	Config::save();
	
	if (execute(realpath(__DIR__.'/../../ssl-config').' '.Config::system('server')) === FALSE) {
		throw new Exception('Failed to configure SSL Certificate');
	}
	
	$_SESSION['success'] = TRUE;
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

if (!empty($_SESSION['firstStart'])) {
	Breadcrumb::go('/network');
	return;
}

Breadcrumb::back();
