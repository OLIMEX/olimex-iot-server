<?php 

class Request {
	
	protected static $api = FALSE;
	
	public static function isHTTPS() {
		return (
			isset($_SERVER['HTTPS']) && 
			(strcasecmp('off', $_SERVER['HTTPS']) !== 0)
		);
	}
	
	public static function host() {
		return (empty($_SERVER['HTTP_HOST']) ? 
			$_SERVER['SERVER_ADDR']
			: 
			$_SERVER['HTTP_HOST']
		);
	}
	
	public static function toHTTPS() {
		return 'https://'.Request::host().$_SERVER['REQUEST_URI'];
	}
	
	public static function parse($URI = NULL) {
		$request = parse_url(
			empty($URI) ?
				$_SERVER['REQUEST_URI']
				:
				$URI
		);
		
		self::$api = preg_match('/(.*)\/api-key\//', $request['path']);
		
		if (preg_match('/(.*)\/api-key\/([A-Za-z0-9]+)$/', $request['path'], $match)) {
			$user = UserManager::getByApiKey($match[2]);
			if (
				empty($user) || 
				(
					UserManager::id() > 0 &&
					$user->id() != UserManager::id() 
				)
			) {
				throw new Exception('Unauthorized', 401);
			}
			
			$request['path'] = $match[1];
			UserManager::current($user);
		} else {
			if (self::$api) {
				throw new Exception('Unauthorized', 401);
			}
		}
		
		return $request;
	}
	
	public static function isAPI() {
		return self::$api;
	}
}
