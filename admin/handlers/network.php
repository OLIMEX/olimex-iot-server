<?php 
if (!Network::canConfigure()) {
	throw new Exception('Access denied', 401);
}

$interface = Network::getInterface(prmPOST('interface'));
$action = prmPOST('action');

if (empty($interface)) {
	if ($action == 'Done' && !empty($_SESSION['firstStart'])) {
		Breadcrumb::go('/config');
		return;
	}
	throw new Exception('Invalid network interface', 400);
}

if ($interface->isServer()) {
	throw new Exception('Can not modify interface you are connected to', 400);
}

if ($interface->isUSB()) {
	throw new Exception('Can not modify USB system interface', 400);
}

if (empty($action)) {
	try {
		if (!UserManager::isAdmin()) {
			throw new Exception('Access denied', 401);
		}
		
		$interface->auto(prmPOST('auto'));
		$interface->dhcp(prmPOST('dhcp'));
		
		if ($interface->isWiFi()) {
			$interface->SSID(prmPOST('ssid'));
			$interface->PSK(prmPOST('psk'));
		}
		
		if ($interface->dhcp() == FALSE) {
			$interface->ip(prmPOST('ip'));
			$interface->mask(prmPOST('mask'));
		}
		
		$interface->writeConfig();
		
		if ($interface->isDown()) {
			if ($interface->auto()) {
				$interface->up();
			}
		} else {
			$interface->down();
			$interface->up();
		}
	} catch (Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	
	Breadcrumb::reload($interface->name());
} else {
	switch (strtolower($action)) {
		case 'up':
			$interface->up();
		break;
		
		case 'down':
			if (!UserManager::isAdmin()) {
				throw new Exception('Access denied', 401);
			}
			$interface->down();
		break;
		
		case 'restart':
			if ($interface->isUp()) {
				$interface->down();
			}
			$interface->up();
		break;
		
		default: throw new Exception('Invalid action', 400);
	}
	
	echo "OK";
}
