<?php

class DB {
	static protected $db = NULL;
	
	public static function init($host, $database, $user, $password) {
		self::$db = new PDO(
			'pgsql:host='.$host.';dbname='.$database, 
			$user, $password, 
			array (
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			)
		);
	}
	
	public static function query($sql, array $parameters = array()) {
		if (empty(self::$db)) {
			throw new Exception('Database not configured');
		}
		
		$stmt = self::$db->prepare($sql);
		$stmt->execute($parameters);
		return $stmt;
	}
	
	public static function __callStatic($name, $arguments) {
		if (empty(self::$db)) {
			throw new Exception('Database not configured');
		}
		
		$function = array(self::$db, $name);
		if (is_callable($function)) {
			return call_user_func_array($function, $arguments);
		}
		throw new Exception('DB method not exists ['.$name.']');
	}
}
