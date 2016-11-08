<?php 
define('SCAN_RETRY', 4);

if (!Network::canScan()) {
	if (Config::system('service.network') == 'admin-only') {
		throw new Exception('Access denied', 401);
	}
	
	$reason = WiFi::check() == 0 ?
		'No WiFi interfaces found.'
		:
		'You are connected to the only WiFi interface available.'
	;
	throw new Exception('WiFi scan can not be done. '.$reason);
}

$user = UserManager::current();
if (empty($user)) {
	throw new Exception('Invalid user', 400);
}

try {
	if ($user->password_verify(prmPOST('password')) == FALSE) {
		throw new Exception('Invalid password');
	}
	
	$iotHost = prmPOST('custom_host');
	if (empty($iotHost)) {
		throw new Exception('IoT Host can not be empty');
	}
	
	$stationSSID = prmPOST('custom_ssid');
	if (empty($stationSSID)) {
		throw new Exception('Station SSID can not be empty');
	}
	
	$stationPSK  = prmPOST('custom_psk');
	if (empty($stationPSK)) {
		throw new Exception('Station PSK can not be empty');
	}
	
	$apPSK = prmPOST('psk');
	if (empty($apPSK)) {
		throw new Exception('Access Point new PSK can not be empty');
	}
	
	$scan = prmPOST('scan', array());
	if (!is_array($scan)) {
		throw new Exception('Invalid POST data');
	}
	
	foreach ($scan as $address => $settings) {
		if (!preg_match('/^([A-F0-9]{2}:){5}([A-F0-9]{2})$/', $address)) {
			throw new Exception('Invalid AP address');
		}
		
		if (empty($settings['config'])) {
			unset($scan[$address]);
			continue;
		}
		
		if (empty($settings['SSID'])) {
			throw new Exception('Missing SSID');
		}
		
		if (empty($settings['name'])) {
			throw new Exception('Missing AP name');
		}
	}
	
	if (empty($scan)) {
		throw new Exception('Please select at least one ESP8266 node');
	}
	
} catch (Exception $e) {
	$_SESSION['error'] = $e->getMessage();
	$_SESSION['post'] = $_POST;
	Breadcrumb::reload();
	return;
}

ignore_user_abort(TRUE);
set_time_limit(0);

session_write_close();
sleep(1);

$canStart = NetworkScan::start();

Breadcrumb::go('/scan/status', FALSE);
fastcgi_finish_request();

if (empty($canStart)) {
	exit;
}

try {
	if (!function_exists('curl_init')) {
		throw new Exception('Missing cURL module');
	}
	
	$interface = WiFi::selectInterface();
	if (empty($interface)) {
		throw new Exception('Can not select WiFi interface. Check network configuration.');
	}
	
	$connected = WiFi::getConnected($interface);
	foreach ($connected as $c) {
		NetworkScan::status('Currently connected to '.$c['interface'].' '.$c['SSID'].' '.$c['currentIP']);
	}
	
	foreach ($scan as $address => $settings) {
		if (empty($settings['config'])) {
			continue;
		}
		
		$AP = new AccessPoint($interface, $address, $settings['SSID']);
		
		$retry = 0;
		do {
			$error = FALSE;
			try {
				NetworkScan::status();
				if ($retry > 0) {
					NetworkScan::status('Retry '.$retry);
				}
				
				NetworkScan::status('Connecting to '.$AP->SSID().'...');
				$AP->connect(AccessPoint::DEFAULT_PSK, '192.168.4.201', '255.255.255.0');
				NetworkScan::status('Connected.');
				
				$iot = new IoT($AP->getNeighbor(1));
				
				NetworkScan::status('Configuring IoT server...');
				$iot->post(
					'/config/iot',
					array(
						'Data' => array(
							'IoT' => array(
								'WebSocket' => 1,
								'Server'    => $iotHost,
								'Path'      => '/events',
								'User'      => $user->name(),
								'Password'  => $user->password(),
								'Name'      => $settings['name'],
							)
						)
					)
				);
				NetworkScan::status('IoT server successfully configured.');
				
				NetworkScan::status('Configuring IoT station...');
				$iot->post(
					'/config/station',
					array(
						'Data' => array(
							'Station' => array(
								'SSID'        => $stationSSID,
								'Password'    => $stationPSK,
								'Hostname'    => $settings['name'],
								'AutoConnect' => 1,
								'DHCP'        => 1,
							)
						)
					)
				);
				NetworkScan::status('IoT station successfully configured.');
				
				NetworkScan::status('Configuring IoT user...');
				$iot->post(
					'/config',
					array(
						'Data' => array(
							'Config' => array(
								'User'        => $user->name(),
								'Password'    => $user->password(),
							)
						)
					)
				);
				NetworkScan::status('IoT user successfully configured.');
				
				$iot->user($user->name());
				$iot->password($user->password());
				
				try {
					NetworkScan::status('Configuring IoT access point...');
					$iot->post(
						'/config/access-point',
						array(
							'Data' => array(
								'AccessPoint' => array(
									'SSID'        => $settings['name'],
									'Password'    => $apPSK
								)
							)
						)
					);
					NetworkScan::status('IoT access point successfully configured.');
				} catch (Exception $e) {
					if ($e->getCode() != CURLE_OPERATION_TIMEOUTED) {
						throw $e;
					}
				}
				
			} catch (Exception $e) {
				NetworkScan::status('<span class="error">'.$e->getMessage().'</span>');
				$error = TRUE;
				if ($e->getCode() == 401) {
					$retry = SCAN_RETRY + 1;
				}
			}
			
			try {
				NetworkScan::status('Disconnecting '.$AP->SSID().'...');
				$AP->disconnect();
				NetworkScan::status('Disconnected.');
			} catch (Exception $e) {
				NetworkScan::status('<span class="error">'.$e->getMessage().'</span>');
			}
		} while ($error && $retry++ < SCAN_RETRY);
	}
	
	NetworkScan::status();
	
	WiFi::deselectInterface();
	WiFi::restoreConnected($connected);
} catch (Exception $e) {
	NetworkScan::status($e->getMessage());
}

NetworkScan::status('Done.');
sleep(5);

NetworkScan::done();
