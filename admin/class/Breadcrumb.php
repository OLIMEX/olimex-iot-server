<?php 

class Breadcrumb {
	
	protected static $BACK        = '/back';
	protected static $LOGIN       = '/login';
	protected static $LOGOUT      = '/logout';
	protected static $FIRST_START = '/first-start';
	
	protected static $history = array();
	protected static $request = array();
	
	protected static $free    = array();
	protected static $local   = array();
	protected static $forced  = array();
	
	public static function free($page) {
		if (!in_array($page, self::$free)) {
			self::$free[] = $page;
		}
	}
	
	public static function local($page) {
		if (!in_array($page, self::$local)) {
			self::$local[] = $page;
		}
	}
	
	public static function forced($configKey, $page) {
		if (!isset(self::$forced[$configKey])) {
			self::$forced[$configKey] = $page;
		}
	}
	
	public static function init(Template $template) {
		if (
			Config::system('ssl') && 
			!Request::isHTTPS() &&
			!Network::getServerInterface()->isUSB() &&
			$_SERVER['REMOTE_ADDR'] != '127.0.0.1'
		) {
			Breadcrumb::go(Request::toHTTPS());
			return;
		}
		
		if (empty($_SESSION['breadcrumb'])) {
			$_SESSION['breadcrumb'] = array();
		}
		
		if (
			$_SERVER['REMOTE_ADDR'] == '127.0.0.1' && 
			in_array($_SERVER['REQUEST_URI'], self::$local)
		) {
			// Node JS - local request
			return;
		}
		
		self::$history = &$_SESSION['breadcrumb'];
		self::$request = Request::parse();
		
		if (self::$request['path'] != self::$BACK && $_SERVER['REQUEST_METHOD'] == 'GET') {
			if ($template->isPage()) {
				$last = end(self::$history);
				if (!empty($last) && self::$request['path'] == $last['path']) {
					array_pop(self::$history);
				}
				self::$history[] = self::$request;
				
				if ($_SERVER['REQUEST_METHOD'] == 'POST') {
					self::$request = array_pop(self::$history);
				}
			}
		} else if (!empty(self::$history)) {
			self::$request = array_pop(self::$history);
		}
		
		if (UserManager::countUsers() > 0) {
			$user = UserManager::current();
			if (empty($user)) {
				if (!in_array(self::$request['path'], self::$free)) {
					Breadcrumb::go(self::$LOGIN);
				}
			} else if (self::$request['path'] != self::$LOGOUT) {
				foreach (self::$forced as $key => $page) {
					if (Config::user($key) && self::$request['path'] != $page) {
						Breadcrumb::go($page);
					}
				}
			}
		} else {
			if (self::$request['path'] != self::$FIRST_START) {
				Breadcrumb::go(self::$FIRST_START);
			}
		}
		
		if ($template->is404()) {
			header('Status: 404 Not Found', TRUE, 404);
			return;
		}
	}
	
	public static function current() {
		return end(self::$history);
	}
	
	protected static function apiResponse() {
		header('Content-Type: application/json');
		if (empty($_SESSION['error'])) {
			echo '{"Status": "OK"}';
		} else {
			echo '{"Status": "'.$_SESSION['error'].'"}';
		}
		exit;
	}
	
	public static function reload($hash = NULL) {
		if (Request::isAPI()) {
			self::apiResponse();
			return;
		}
		
		header('Location: '.
			self::$request['path'].
			(empty(self::$request['query']) ? '' : '?'.self::$request['query']).
			(empty($hash) ? 
				(empty(self::$request['fragment']) ? '' : '#'.self::$request['fragment']) 
				: 
				'#'.$hash
			)
		);
	}
	
	public static function back() {
		if (Request::isAPI()) {
			self::apiResponse();
			return;
		}
		
		$back = array_pop(self::$history);
		if (empty($back)) {
			header('Location: /');
			return;
		}
		
		header('Location: '.$back['path'].(empty($back['query']) ? '' : '?'.$back['query']));
	}
	
	public static function go($location, $immediate = TRUE) {
		if (Request::isAPI()) {
			self::apiResponse();
			return;
		}
		
		if (headers_sent()) {
			echo '<a href="'.$location.'">Redirect...</a><script>setTimeout(function () {window.location.href="'.$location.'";}, 5000);</script>';
		} else {
			header('Location: '.$location);
		}
		
		if ($immediate) {
			session_write_close();
			die;
		}
	}
}
