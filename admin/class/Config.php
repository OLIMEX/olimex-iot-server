<?php 

class Config {
	
	protected static $default = [];
	protected static $system  = [];
	protected static $user    = [];
	
	public static function init($config_file) {
		$config_path = realpath($config_file);
		
		if (empty($config_path)) {
			throw new Exception('Missing configuration file');
		}
		
		self::$system = json_decode(
			file_get_contents($config_path),
			TRUE
		);
		
		if (isset(self::$system['default'])) {
			self::$default = self::$system['default'];
			unset(self::$system['default']);
		}
		
		if (empty(self::$system['pg'])) {
			throw new Exception('Missing database configuration [pg]');
		}
		
		DB::init(
			self::$system['pg']['host'],
			self::$system['pg']['database'],
			self::$system['pg']['user'],
			self::$system['pg']['password']
		);
		
		self::userInit(UserManager::id());
	}
	
	public static function userInit($userID) {
		$config = DB::query(
			'SELECT * FROM "Config" WHERE "userID" IS NULL OR "userID" = :userID',
			array(':userID' => $userID)
		)->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($config as $c) {
			$userID = self::index($c['userID']);
			self::$user[$userID] = json_decode($c['data'], TRUE);
		}
	}
	
	protected static function index($id) {
		return '_'.$id.'_';
	}
	
	public static function options() {
		return array(
			'system' => array(
				'service' => array(
					'network' => array(
						'admin-only' => NULL,
						'all-users' => NULL
					)
				),
				'nodes' => array(
					'unknown' => array(
						'user'   => NULL,
						'reject' => NULL
					)
				)
			),
			'user' => array(
				'nodes' => array(
					'accept' => array(
						'auto'   => NULL,
						'manual' => NULL,
						'reject' => NULL
					)
				)
			)
		);
	}
	
	protected static function get(&$config, $name) {
		$split = preg_split('/\./', $name);
		foreach ($split as $s) {
			if (!isset($config[$s])) {
				return NULL;
			}
			$config = &$config[$s];
		}
		return $config;
	}
	
	protected static function set(&$config, $name, $value) {
		$split = preg_split('/\./', $name);
		$last  = count($split) - 1;
		foreach ($split as $i => $s) {
			if ($i != $last) {
				if (!isset($config[$s])) {
					$config[$s] = array();
				}
				$config = &$config[$s];
				continue;
			}
			
			$config[$s] = is_numeric($value) ?
				$value + 0
				:
				$value
			;
		}
	}
	
	public static function system($name, $value = NULL) {
		if (func_num_args() == 1) {
			$value = self::get(self::$system, $name);
			if ($value !== NULL) {
				return $value;
			}
			
			$value = self::get(self::$user, self::index(NULL).'.'.$name);
			if ($value !== NULL) {
				return $value;
			}
			
			return self::get(self::$default, $name);
		}
		
		self::set(self::$user, self::index(NULL).'.'.$name, $value);
	}
	
	public static function user($name, $value = NULL) {
		$index = self::index(UserManager::id());
		
		if (func_num_args() == 1) {
			$value = self::get(self::$user, $index.'.'.$name);
			if ($value !== NULL) {
				return $value;
			}
			
			return self::get(self::$default, $name);
		}
		
		self::set(self::$user, $index.'.'.$name, $value);
	}
	
	protected static function dbStore($userID, $data) {
		$where = (empty($userID) ?
			'WHERE "userID" IS NULL'
			:
			'WHERE "userID" = :userID'
		);
		
		$exists = DB::query(
			'SELECT COUNT(*) FROM "Config" '.$where,
			empty($userID) ? array() : array(':userID' => $userID)
		)->fetchColumn();
		
		if (empty($exists)) {
			DB::query(
				'INSERT INTO "Config" ("userID", "data") VALUES (:userID, :data)',
				array(':userID' => $userID, ':data' => $data)
			);
		} else {
			DB::query(
				'UPDATE "Config" SET "userID" = :userID, "data" = :data '.$where,
				array(':userID' => $userID, ':data' => $data)
			);
		}
	}
	
	public static function save() {
		$user = UserManager::current();
		$index = self::index($user->id());
		
		if ($user->isAdmin()) {
			self::dbStore(
				NULL, 
				json_encode(
					self::get(self::$user, self::index(NULL))
				)
			);
		}
		
		self::dbStore(
			$user->id(), 
			json_encode(
				self::get(self::$user, $index)
			)
		);
	}
}
