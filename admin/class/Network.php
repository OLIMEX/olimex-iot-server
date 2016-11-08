<?php 

class NetworkScan {
	const STATE = '/ap_scan_state';
	const TEMP  = '/ap_scan_temp';
	const WAIT  = '/ap_scan_wait';
	
	const DONE  = '/ap_scan_done';
	
	protected static function stream($file = NetworkScan::STATE) {
		return sys_get_temp_dir().$file;
	}
	
	public static function isStarted() {
		return file_exists(self::stream());
	}
	
	public static function start($message = 'Initializing...') {
		if (self::isStarted()) {
			return FALSE;
		}
		
		self::done(NetworkScan::DONE);
		file_put_contents(self::stream(), $message."\n");
		return TRUE;
	}
	
	public static function done($file = NetworkScan::STATE) {
		if ($file == NetworkScan::STATE) {
			@rename(self::stream($file), self::stream(NetworkScan::DONE));
		} else {
			@unlink(self::stream($file));
		}
	}
	
	public static function status($message = '') {
		if (is_array($message)) {
			$message = var_export($message, TRUE);
		}
		file_put_contents(self::stream(), $message."\n", FILE_APPEND);
		self::done(NetworkScan::TEMP);
	}
	
	public static function temp($message = '', $mode = FILE_APPEND) {
		file_put_contents(self::stream(NetworkScan::TEMP), $message."\n", $mode);
	}
	
	public static function wait($message = '', $button = 'Continue') {
		if (is_array($message)) {
			$message = var_export($message, TRUE);
		}
		
		$message .= "\n".'<button type="button" class="continue">'.$button.'</button>'."\n";
		
		file_put_contents(self::stream(NetworkScan::WAIT), $message);
		while (file_exists(self::stream(NetworkScan::WAIT))) {
			sleep(1);
		}
	}
	
	public static function getStatus() {
		return 
			@file_get_contents(self::stream(NetworkScan::DONE)).
			@file_get_contents(self::stream(NetworkScan::STATE)).
			@file_get_contents(self::stream(NetworkScan::TEMP)).
			@file_get_contents(self::stream(NetworkScan::WAIT))
		;
	}
}

class Network {
	protected static $interfaces = array();
	
	protected static $config_file = '/etc/network/interfaces';
	protected static $config_dir  = '/etc/network/interfaces.d/';
	protected static $config_cmd  = NULL;
	
	protected static function init() {
		static $init = TRUE;
		if ($init) {
			self::$config_cmd = realpath(__DIR__.'/../../net-config');
			$init = FALSE;
		}
	}
	
	public static function configFile($file = NULL) {
		self::init();
		if (func_num_args() == 0) {
			return self::$config_file;
		}
		
		self::$config_file = realpath($file);
	}
	
	public static function trimConfigLine($line) {
		$line = preg_replace('/#.*/', '', $line);
		$line = preg_replace('/[\s\t\r\n]+/', ' ', $line);
		$line = trim($line);
		return $line;
	}
	
	public static function commentConfig(NetworkInterface $interface) {
		$config = file(self::configFile());
		if ($config === FALSE) {
			return FALSE;
		}
		
		$started = FALSE;
		foreach ($config as &$line) {
			$l = self::trimConfigLine($line);
			
			if (preg_match('/^(auto|allow-auto|allow-hotplug|iface|mapping) (\S+)/', $l, $match)) {
				if ($interface->name() == $match[2]) {
					$started = in_array($match[1], array('iface', 'mapping'));
					$line = '# '.$line;
					continue;
				}
				$started = FALSE;
			}
			
			if ($started) {
				$line = '# '.$line;
			}
		}
		
		$file = sys_get_temp_dir().'/interfaces';
		file_put_contents($file, join('', $config));
		execute(self::configCmd().' '.$file);
	}
	
	public static function configDir($dir = NULL) {
		self::init();
		if (func_num_args() == 0) {
			return self::$config_dir;
		}
		
		$dir = realpath($dir);
		if (empty($dir)) {
			self::$config_dir = NULL;
		} else {
			self::$config_dir = $dir.'/';
		}
	}
	
	public static function configCmd($cmd = NULL) {
		self::init();
		if (func_num_args() == 0) {
			return self::$config_cmd;
		}
		
		$cmd = realpath($cmd);
		if (empty($cmd)) {
			self::$config_cmd = NULL;
		} else {
			self::$config_cmd = $cmd;
		}
	}
	
	public static function getInterface($name) {
		self::findInterfaces();
		return (isset(self::$interfaces[$name]) ?
			self::$interfaces[$name]
			:
			NULL
		);
	}
	
	public static function getInterfaces() {
		self::findInterfaces();
		return self::$interfaces;
	}
	
	public static function getServerInterface() {
		self::findInterfaces();
		foreach (self::$interfaces as $interface) {
			if ($interface->isServer()) {
				return $interface;
			}
		}
		return NULL;
	}
	
	public static function canConfigure() {
		return (
			Config::system('service.network') != 'admin-only'
			||
			UserManager::isAdmin()
		);
	}
	
	public static function canScan() {
		if (!self::canConfigure()) {
			return FALSE;
		}
		
		$wifiCount = WiFi::check();
		$server = self::getServerInterface();
		return (
			$wifiCount > 0 && 
			(
				$wifiCount > 1 || 
				($server != NULL && !$server->isWiFi())
			)
		);
	}
	
	protected static function findInterfaces($force = FALSE) {
		if (empty($force) && !empty(self::$interfaces)) {
			return;
		}
		
		self::$interfaces = array();
		
		$result = execute('ifconfig -a');
		if ($result === FALSE) {
			throw new Exception('Can not initialize Network interfaces');
		}
		
		$name      = NULL;
		$address   = NULL;
		$linkEncap = NULL;
		$ip        = NULL;
		$mask      = NULL;
		$up        = FALSE;
		$running   = FALSE;
		foreach ($result as $line) {
			if ($line == '') {
				self::$interfaces[$name] = WiFi::check($name) ?
					new WiFiInterface($name, $address, $linkEncap, $ip, $mask, $up, $running)
					:
					new NetworkInterface($name, $address, $linkEncap, $ip, $mask, $up, $running)
				;
				
				$name      = NULL;
				$address   = NULL;
				$linkEncap = NULL;
				$ip        = NULL;
				$mask      = NULL;
				$up        = FALSE;
				$running   = FALSE;
			}
			
			if (preg_match('/^(\S+)\s+.*$/', $line, $match)) {
				$name = $match[1];
			}
			
			if (preg_match('/HWaddr\s+(\S+)/', $line, $match)) {
				$address = strtoupper($match[1]);
			}
			
			if (preg_match('/Link encap:(\S+)/', $line, $match)) {
				$linkEncap = $match[1];
			}
			
			if (preg_match('/inet addr:(\S+)/', $line, $match)) {
				$ip = $match[1];
			}
			
			if (preg_match('/Mask:(\S+)/', $line, $match)) {
				$mask = $match[1];
			}
			
			if (preg_match('/\s+UP\s+/', $line)) {
				$up = TRUE;
			}
			
			if (preg_match('/\s+RUNNING\s+/', $line)) {
				$running = TRUE;
			}
			
		}
	}
	
}

class WiFi {
	
	protected static $accessPoints   = array();
	protected static $interfaceNames = array();
	
	protected static $selectedInterface = NULL; 
	protected static $selectedDown      = FALSE; 
	
	public static function check($interface = NULL) {
		self::findInterfaces();
		
		if (func_num_args() == 0) {
			return count(self::$interfaceNames);
		}
		
		return in_array($interface, self::$interfaceNames);
	}
	
	public static function getInterfaceNames() {
		self::findInterfaces();
		return self::$interfaceNames;
	}
	
	public static function getAccessPoints() {
		self::scanAccessPoints();
		return self::$accessPoints;
	}
	
	public static function getAP($SSID) {
		self::scanAccessPoints();
		foreach (self::$accessPoints as $AP) {
			if ($AP->SSID() == $SSID) {
				return $AP;
			}
		}
		
		return NULL;
	}
	
	public static function getConnected(NetworkInterface $selected = NULL) {
		$connected = array();
		foreach (Network::getInterfaces() as $interface) {
			if (!$interface->isWiFi()) {
				continue;
			}
			
			if (!$interface->isConfigured()) {
				continue;
			}
			
			if (!empty($selected) && $selected->name() != $interface->name()) {
				continue;
			}
			
			foreach ($interface->networks() as $network) {
				if ($network['connected']) {
					$dhcp = $interface->dhcp();
					$connected[] = array(
						'interface' => $interface->name(),
						'network'   => $network['id'],
						'SSID'      => $network['SSID'],
						'currentIP' => $interface->ip(),
						'ip'        => $dhcp ? NULL : $interface->ip(),
						'mask'      => $dhcp ? NULL : $interface->mask(),
					);
				}
			}
		}
		return $connected;
	}
	
	public static function restoreConnected($connected) {
		foreach ($connected as $c) {
			NetworkScan::status('Restoring connection to '.$c['SSID'].'...');
			WPA::connect($c['interface'], $c['network'], $c['ip'], $c['mask']);
			NetworkScan::status();
		}
	}
	
	protected static function findInterfaces($force = FALSE) {
		if (empty($force) && !empty(self::$interfaces)) {
			return;
		}
		
		self::$interfaceNames = array();
		
		$result = execute('iw dev');
		if ($result === FALSE) {
			throw new Exception('Can not initialize WiFi interfaces');
		}
		
		foreach ($result as $line) {
			if (preg_match('/^\s*Interface\s+(\S+)\s*$/', $line, $match)) {
				self::$interfaceNames[] = $match[1];
			}
		}
	}
	
	public static function selectInterface() {
		if (!empty(self::$selectedInterface)) {
			return self::$selectedInterface;
		}
		
		foreach (Network::getInterfaces() as $i) {
			if (!$i->isWiFi()) {
				continue;
			}
			
			if ($i->isServer()) {
				continue;
			}
			
			self::$selectedInterface = $i;
			if (self::$selectedInterface->isUp()) {
				break;
			}
		}
		
		if (empty(self::$selectedInterface)) {
			return NULL;
		}
		
		self::$selectedDown = self::$selectedInterface->isDown();
		if (self::$selectedDown) {
			self::$selectedInterface->up(FALSE);
		}
		
		return self::$selectedInterface;
	}
	
	public static function deselectInterface() {
		if (self::$selectedDown) {
			self::$selectedInterface->down(FALSE);
		}
		self::$selectedInterface = NULL;
	}
	
	protected static function scanAccessPoints($force = FALSE) {
		if (empty($force) && !empty(self::$accessPoints)) {
			return;
		}
		
		self::$accessPoints = array();
		
		$interface = self::selectInterface();
		if (empty($interface)) {
			return;
		}
		
		$result = execute('iw '.$interface->name().' scan');
		if ($result === FALSE) {
			throw new Exception('Can not scan WiFi APs '.$interface->name());
		}
		
		$AP = NULL;
		try {
			$status = WPA::status($interface->name());
		} catch (Exception $e) {
			$status = NULL;
		}
		
		foreach ($result as $line) {
			if (preg_match('/BSS\s+([A-Fa-f\d:]+)/', $line, $match)) {
				if (self::findAP($match[1]) !== NULL) {
					$AP = NULL;
					continue;
				}
				self::$accessPoints[] = $AP = new AccessPoint($interface, $match[1]);
			}
			
			if ($AP === NULL) {
				continue;
			}
			
			if (preg_match('/SSID:\s+([\S\s]+)$/', $line, $match)) {
				$AP->SSID($match[1]);
				$AP->interfaceStatus($status);
			}
		}
		
		self::deselectInterface();
	}
	
	protected static function findAP($address) {
		$address = strtoupper($address);
		foreach (self::$accessPoints as $AP) {
			if ($AP->address() == $address) {
				return $AP;
			}
		}
		return NULL;
	}
}

class NetworkInterface {
	
	protected $name      = NULL;
	protected $address   = NULL;
	protected $linkEncap = NULL;
	protected $auto      = FALSE;
	protected $dhcp      = FALSE;
	protected $ip        = NULL;
	protected $mask      = NULL;
	protected $up        = NULL;
	protected $running   = NULL;
	protected $driver    = NULL;
	
	public function __construct($name, $address, $linkEncap, $ip, $mask, $up, $running) {
		$this->name      = $name;
		$this->address   = $address;
		$this->linkEncap = $linkEncap;
		$this->up        = $up;
		$this->running   = $running;
		
		$this->readConfig();
		$this->getDriver();
		
		if (!empty($ip)) {
			$this->ip = $ip;
		}
		
		if (!empty($mask)) {
			$this->mask = $mask;
		}
	}
	
	public function name() {
		return $this->name;
	}
	
	public function address() {
		return $this->address;
	}
	
	public function driver() {
		return $this->driver;
	}
	
	public function isUp() {
		return $this->up;
	}
	
	public function isDown() {
		return !$this->up;
	}
	
	public function isRunning() {
		return $this->running;
	}
	
	public function auto($auto = NULL) {
		if (func_num_args() == 0) {
			return $this->auto;
		}
		$this->auto = !empty($auto);
	}
	
	public function dhcp($dhcp = NULL) {
		if (func_num_args() == 0) {
			return $this->dhcp;
		}
		$this->dhcp = !empty($dhcp);
	}
	
	public function ip($ip = NULL) {
		if (func_num_args() == 0) {
			return $this->ip;
		}
		
		if (inet_pton($ip) === FALSE) {
			throw new Exception('Invalid IP address');
		}
		$this->ip = $ip;
	}
	
	public function mask($mask = NULL) {
		if (func_num_args() == 0) {
			return $this->mask;
		}
		
		if (inet_pton($mask) === FALSE) {
			throw new Exception('Invalid network mask');
		}
		$this->mask = $mask;
	}
	
	public function isEthernet() {
		return $this->linkEncap == 'Ethernet';
	}
	
	public function isUSB() {
		return $this->driver == 'g_ether';
	}
	
	public function isWiFi() {
		return FALSE;
	}
	
	public function isServer() {
		$server_ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
		return $this->ip == $server_ip;
	}
	
	protected function getDriver() {
		$result = execute('ethtool -i '.$this->name);
		if ($result === FALSE) {
			$this->driver = NULL;
			return;
		}
		
		foreach ($result as $line) {
			if (preg_match('/^driver:\s+(.+)$/', $line, $match)) {
				$this->driver = $match[1];
			}
		}
	}
	
	protected function configFile() {
		return Network::configDir().$this->name;
	}
	
	public function isConfigured() {
		return file_exists($this->configFile());
	}
	
	protected function parseConfig(array &$config) {
		foreach ($config as &$line) {
			$line = Network::trimConfigLine($line);
			
			if (preg_match('/^hwaddress ether (\S+)/', $line, $match)) {
				$this->address = strtoupper($match[1]);
				continue;
			}
			
			if (preg_match('/^auto '.$this->name.'/', $line)) {
				$this->auto = TRUE;
				continue;
			}
			
			if (preg_match('/^iface '.$this->name.' inet dhcp/', $line)) {
				$this->dhcp = TRUE;
				continue;
			}
			
			if (preg_match('/^address (\S+)/', $line, $match)) {
				$this->ip = $match[1];
				continue;
			}
			
			if (preg_match('/^netmask (\S+)/', $line, $match)) {
				$this->mask = $match[1];
				continue;
			}
		}
	}
	
	public function readConfig() {
		if (!$this->isEthernet()) {
			return;
		}
		
		if (!$this->isConfigured()) {
			return;
		}
		
		$config = file($this->configFile());
		if ($config === FALSE) {
			return;
		}
		
		$this->parseConfig($config);
	}
	
	protected function prepareConfig() {
		$config = array();
		
		if ($this->auto) {
			$config[] = 'auto '.$this->name;
		}
		
		$config[] = 'iface '.$this->name.' inet '.($this->dhcp ? 'dhcp' : 'static');
		
		if ($this->isEthernet() && !$this->isWiFi() && !empty($this->address)) {
			$config[] = 'hwaddress ether '.$this->address;
		}
		
		if (empty($this->dhcp)) {
			$config[] = 'address '.$this->ip;
			$config[] = 'netmask '.$this->mask;
		}
		
		return $config;
	}
	
	public function writeConfig() {
		if (!$this->isEthernet()) {
			return;
		}
		
		Network::commentConfig($this);
		
		$config = $this->prepareConfig();
		$file = sys_get_temp_dir().'/'.$this->name;
		file_put_contents($file, join("\n", $config)."\n\n");
		execute(Network::configCmd().' '.$file);
	}
	
	public function up($highLevel = TRUE) {
		if ($this->isUp()) {
			return;
		}
		
		if ($this->isConfigured() && $highLevel) {
			$result = execute('ifup '.$this->name);
		} else {
			$result = $this->isWiFi() ?
				WPA::start($this->name)
				:
				TRUE
			;
			if ($result !== FALSE) {
				$result = execute('ifconfig '.$this->name.' up');
			}
		}
		
		$this->up = ($result !== FALSE);
	}
	
	public function down($highLevel = TRUE) {
		if ($this->isDown()) {
			return;
		}
		
		if ($this->isConfigured() && $highLevel) {
			$result = execute('ifdown '.$this->name);
		} else {
			$result = $this->isWiFi() ?
				execute('ifconfig '.$this->name.' down')
				:
				TRUE
			;
			if ($result !== FALSE) {
				$result = WPA::stop($this->name);
			}
		}
		
		$this->up = !($result !== FALSE);
	}
	
	public function __toString() {
		return $this->name.($this->ip != NULL ? ' ['.$this->ip.']' : '');
	}
}

class WiFiInterface extends NetworkInterface {
	protected $SSID      = NULL;
	protected $PSK       = NULL;
	
	protected $state     = NULL;
	protected $networks  = array();
	
	public function __construct($name, $address, $linkEncap, $ip, $mask, $up, $running) {
		NetworkInterface::__construct($name, $address, $linkEncap, $ip, $mask, $up, $running);
		$this->updateStatus();
	}
	
	public function isWiFi() {
		return TRUE;
	}
	
	public function SSID($SSID = NULL) {
		if (func_num_args() == 0) {
			return $this->SSID;
		}
		
		$this->SSID = $SSID;
	}
	
	public function PSK($PSK = NULL) {
		if (func_num_args() == 0) {
			return $this->PSK;
		}
		
		$this->PSK = $PSK;
	}
	
	public function networks() {
		return $this->networks;
	}
	
	protected function parseConfig(array &$config) {
		NetworkInterface::parseConfig($config);
		foreach ($config as $line) {
			if (preg_match('/^wpa-ssid (\S+)/', $line, $match)) {
				$this->SSID = $match[1];
				continue;
			}
			
			if (preg_match('/^wpa-psk (\S+)/', $line, $match)) {
				$this->PSK = $match[1];
				continue;
			}
		}
	}
	
	protected function prepareConfig() {
		$config = NetworkInterface::prepareConfig();
		$config[] = 'wpa-ssid '.$this->SSID;
		$config[] = 'wpa-psk '.$this->PSK;
		
		return $config;
	}
	
	public function updateStatus() {
		if ($this->isDown()) {
			return;
		}
		
		try {
			$this->networks = WPA::networks($this->name);
			$status = WPA::status($this->name);
		} catch (Exception $e) {
			$this->networks = array();
			return;
		}
		
		if (!empty($status['ip'])) {
			$this->ip = $status['ip'];
		}
		
		if (!empty($status['SSID'])) {
			$this->SSID = $status['SSID'];
		}
		
		$this->state = $status['state'];
		
		$connected = !empty($this->ip) && !empty($this->SSID) && ($this->state == 'COMPLETED');
		foreach ($this->networks as &$network) {
			$network['connected'] = $connected && ($this->SSID == $network['SSID']);
			if ($network['connected']) {
				$network['ip'] = $this->ip;
			} else {
				$network['ip'] = NULL;
			}
		}
	}
	
	public function __toString() {
		return NetworkInterface::__toString().' [WiFi]';
	}
}

class AccessPoint {
	
	const DEFAULT_PSK = 'olimex-ap';
	
	protected $interface = NULL;
	protected $SSID      = NULL;
	protected $PSK       = NULL;
	protected $address   = NULL;
	protected $ip        = NULL;
	
	protected $connected    = FALSE;
	protected $wpa_network  = NULL;
	
	public function __construct(WiFiInterface $interface, $address = NULL, $SSID = NULL) {
		$this->interface = $interface;
		$this->address = empty($address) ? NULL : strtoupper($address);
		$this->SSID = $SSID;
	}
	
	public function isESP8266() {
		return (bool)preg_match('/^1A:FE:34:/', $this->address);
	}
	
	public function interfaceName() {
		if (empty($this->interface)) {
			throw new Exception('Network interface is not set');
		}
		return $this->interface->name();
	}
	
	public function interfaceIsUp() {
		if (empty($this->interface)) {
			throw new Exception('Network interface is not set');
		}
		return $this->interface->isUp();
	}
	
	public function interfaceIsDown() {
		if (empty($this->interface)) {
			throw new Exception('Network interface is not set');
		}
		return $this->interface->isDown();
	}
	
	public function address() {
		return $this->address;
	}
	
	public function SSID($SSID = NULL) {
		if (func_num_args() == 0) {
			return $this->SSID;
		}
		$this->SSID = $SSID;
	}
	
	public function PSK($PSK = NULL) {
		if (func_num_args() == 0) {
			return $this->PSK;
		}
		$this->PSK = $PSK;
	}
	
	public function ip() {
		return $this->ip;
	}
	
	public function connected() {
		return $this->connected;
	}
	
	public function getNeighbor($i) {
		if (empty($this->connected) || empty($this->ip)) {
			return NULL;
		}
		
		$ip = inet_pton($this->ip);
		$ip[3] = chr($i);
		return inet_ntop($ip);
	}
	
	public function interfaceStatus($status) {
		if (empty($status) || empty($status['SSID']) || $status['SSID'] != $this->SSID) {
			return;
		}
		
		$this->ip          = $status['ip'];
		$this->wpa_network = $status['id'];
		$this->connected   = ($status['state'] == 'COMPLETED');
		
		if (empty($this->interface)) {
			throw new Exception('Network interface is not set');
		}
		$this->PSK($this->interface->PSK());
	}
	
	public function connect($PSK = AccessPoint::DEFAULT_PSK, $ip = NULL, $mask = NULL) {
		if ($this->interfaceIsDown()) {
			return;
		}
		
		if ($this->connected) {
			return;
		}
		
		if (empty($PSK)) {
			throw new Exception('Missing password when trying to connect to '.$this->SSID);
		}
		
		try {
			$this->wpa_network = WPA::add_network($this->interfaceName(), $this->SSID, $PSK);
			WPA::disconnect($this->interfaceName());
			$this->ip = WPA::connect($this->interfaceName(), $this->wpa_network, $ip, $mask);
			$this->connected = TRUE;
		} catch (Exception $e) {
			$this->disconnect(TRUE);
			throw $e;
		}
	}
	
	public function disconnect($force = FALSE) {
		if ($this->connected || $force) {
			WPA::disconnect($this->interfaceName());
			$this->ip = NULL;
			$this->connected = FALSE;
		}
		
		if ($this->wpa_network !== NULL) {
			WPA::remove_network($this->interfaceName(), $this->wpa_network);
			$this->wpa_network = NULL;
		}
	}
	
	public function updateStatus() {
		$this->interfaceStatus(
			WPA::status($this->interface->name())
		);
		return $this;
	}
	
	public function __toString() {
		return 
			$this->SSID.' ['.$this->address.'] '.
			$this->interfaceName().
			($this->connected ? ' [CONNECTED]' : '').
			($this->ip ? ' ['.$this->ip.']' : '')
		;
	}
}

class WPA {
	
	const CONNECT_TIMEOUT = 15;
	
	static protected $set_network = array(
		array('auth_alg', 'OPEN'),
		array('key_mgmt', 'WPA-PSK'),
		array('proto',    'RSN'),
		array('mode',     '0'),
		array('ssid',     NULL),
		array('psk',      NULL),
	);
	
	static protected $drivers = array();
	
	protected static function getDrivers() {
		if (empty(self::$drivers)) {
			$result = execute('wpa_supplicant -h');
			if ($result === FALSE) {
				throw new Exception('Can not query WPA drivers');
			}
			
			$start = FALSE;
			foreach ($result as $line) {
				if (preg_match('/^drivers:/', $line)) {
					$start = TRUE;
					continue;
				}
				
				if ($start && preg_match('/^\S/', $line)) {
					break;
				}
				
				if ($start && preg_match('/^\s+(\S+)\s+=/', $line, $match)) {
					if (!in_array($match[1], array('none', 'wired'))) {
						self::$drivers[] = $match[1];
					}
				}
			}
			
			if (empty(self::$drivers)) {
				throw new Exception('No WPA drivers found');
			}
		}
		
		return self::$drivers;
	}
	
	public static function start($interface) {
		return execute('wpa_supplicant -s -B -P /run/wpa_supplicant.'.$interface.'.pid -i '.$interface.' -D '.join(',', self::getDrivers()).' -C /run/wpa_supplicant');
	}
	
	public static function stop($interface) {
		return execute('wpa_action '.$interface.' stop');
	}
	
	public static function networks($interface) {
		$result = execute('wpa_cli -i '.$interface.' list_networks');
		if ($result === FALSE) {
			throw new Exception('Can not list WPA networks '.$interface);
		}
		
		$networks = array();
		foreach ($result as $line) {
			if (preg_match('/^(\d+)\s+(\S+)\s+(\S+)\s+(\[\w+\])?$/', $line, $match)) {
				$networks[] = array(
					'id'        => $match[1],
					'SSID'      => $match[2],
					'BSSID'     => $match[3],
					'flags'     => isset($match[4]) ? $match[4] : NULL
				);
			}
		}
		
		return $networks;
	}
	
	public static function status($interface) {
		$result = execute('wpa_cli -i '.$interface.' status');
		if ($result === FALSE) {
			throw new Exception('Can not check WPA status '.$interface);
		}
		
		$status = array(
			'id'    => NULL,
			'ip'    => NULL,
			'SSID'  => NULL,
			'BSSID' => NULL,
			'state' => NULL
		);
		foreach ($result as $line) {
			if (preg_match('/^id=(.+)$/', $line, $match)) {
				$status['id'] = $match[1];
			}
			if (preg_match('/^ip_address=(.+)$/', $line, $match)) {
				$status['ip'] = $match[1];
			}
			if (preg_match('/^ssid=(.+)$/', $line, $match)) {
				$status['SSID'] = $match[1];
			}
			if (preg_match('/^bssid=(.+)$/', $line, $match)) {
				$status['BSSID'] = strtoupper($match[1]);
			}
			if (preg_match('/^wpa_state=(.+)$/', $line, $match)) {
				$status['state'] = $match[1];
			}
		}
		return $status;
	}
	
	public static function add_network($interface, $SSID, $PSK) {
		$result = execute('wpa_cli -i '.$interface.' add_network');
		if ($result === FALSE) {
			throw new Exception('Can not add WPA network '.$interface);
		}
		$network = $result[0];
		
		// WPA set network parameters
		foreach (self::$set_network as $cmd) {
			switch ($cmd[0]) {
				case 'ssid':
					$cmd[1] = '\'"'.$SSID.'"\'';
				break;
				
				case 'psk':
					$cmd[1] = '\'"'.$PSK.'"\'';
				break;
			}
			
			$result = execute('wpa_cli -i '.$interface.' set_network '.$network.' '.join(' ', $cmd));
			if ($result === FALSE) {
				throw new Exception('Can not set WPA network parameter '.$cmd[0]);
			}
		}
		
		return $network;
	}
	
	public static function remove_network($interface, $network) {
		$result = execute('wpa_cli -i '.$interface.' remove_network '.$network);
		if ($result === FALSE) {
			throw new Exception('Can not remove WPA network');
		}
	}
	
	public static function connect($interface, $network, $ip = NULL, $mask = NULL) {
		$result = execute('wpa_cli -i '.$interface.' enable_network '.$network);
		if ($result === FALSE) {
			throw new Exception('Can not enable WPA network');
		}
		
		execute('wpa_cli -i '.$interface.' select_network '.$network);
		if ($result === FALSE) {
			throw new Exception('Can not select WPA network');
		}
		
		execute('wpa_cli -i '.$interface.' reassociate');
		if ($result === FALSE) {
			throw new Exception('Can not reassociate WPA network');
		}
		
		$ready = FALSE;
		$start = time();
		do {
			$status = self::status($interface);
			$ready = ($status['state'] == 'COMPLETED');
			// NetworkScan::status($status['state']);
			
			if (
				in_array(
					$status['state'], 
					array(
						'DISCONNECTED',
						'INTERFACE_DISABLED',
						'INACTIVE'
					)
				)
			) {
				throw new Exception('Can not connect to the AP ['.$status['state'].']');
			}
			
			if (time() - $start > self::CONNECT_TIMEOUT) {
				throw new Exception('Can not connect to the AP [Timeout]');
			}
			sleep(1);
		} while (!$ready);
		NetworkScan::status('Connected.');
		
		if (empty($ip)) {
			NetworkScan::status('Obtaining IP address...');
			$result = execute(
				'dhclient -v -pf /run/dhclient.'.$interface.'.pid -lf /var/lib/dhcp/dhclient.'.$interface.'.leases '.$interface
			);
			if ($result === FALSE) {
				throw new Exception('DHCP obtain IP failed.');
			}
		} else {
			NetworkScan::status('Set IP address...');
			$result = execute('ifconfig '.$interface.' '.$ip.(empty($mask) ? '' : ' netmask '.$mask));
			if ($result === FALSE) {
				throw new Exception('Set IP address failed.');
			}
		}
		
		$count = 0;
		do {
			sleep(1);
			$status = self::status($interface);
		} while (empty($status['ip']) && $count++ < 5);
		
		if (empty($status['ip'])) {
			throw new Exception('Failed to obtain/set IP.');
		}
		
		NetworkScan::status('Ready '.$status['ip']);
		return $status['ip'];
	}
	
	public static function disconnect($interface) {
		execute('dhclient -v -pf /run/dhclient.'.$interface.'.pid -lf /var/lib/dhcp/dhclient.'.$interface.'.leases -r '.$interface);
		execute('wpa_cli -i '.$interface.' disconnect');
	}
}
