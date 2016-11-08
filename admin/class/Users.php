<?php 

class User {
	const USER_LEN          = 64;
	
	protected $id            = NULL;
	protected $name          = '';
	protected $password      = '';
	protected $password_hash = '';
	protected $isAdmin       = false;
	protected $email         = '';
	protected $apiKey        = '';
	
	public function __construct($id = -1, $name = '', $password = '', $isAdmin = false, $email =  '', $apiKey = '') {
		$this->id       = $id;
		$this->name     = $name;
		
		$this->isAdmin  = $isAdmin;
		$this->email    = $email;
		$this->apiKey   = empty($apiKey) ? $this->newApiKey() : $apiKey;
		
		$this->password($password, TRUE);
	}
	
	public function id() {
		return $this->id;
	}
	
	public function validate() {
		if ($this->id <= 0) {
			$this->name($this->name);
		}
		$this->password($this->password);
		$this->email($this->email);
	}
	
	public function name($name = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->name;
		}
		
		if (!$force) {
			if ($this->id > 0) {
				throw new Exception('Username can not be changed');
			}
			
			if (empty($name)) {
				throw new Exception('Username can not be empty');
			}
			
			if (strlen($name) > User::USER_LEN) {
				throw new Exception('Username is too long. Maximum size is '.User::USER_LEN.' chars.');
			}
			
			if (preg_match('/[^A-Za-z0-9@_\-\.]/', $name)) {
				throw new Exception('Username contains invalid characters');
			}
			
			$duplicate = UserManager::getByName($name);
			if (!empty($duplicate) && $duplicate->id() != $this->id) {
				throw new Exception('Username already exists');
			}
		}
		
		$this->name = $name;
	}
	
	public function password($password = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			if (empty($this->password)) {
				throw new Exception('Password can not be empty');
			}
			return $this->password;
		}
		
		if (!$force) {
			if (empty($password)) {
				throw new Exception('Password can not be empty');
			}
			
			if (strlen($password) > User::USER_LEN) {
				throw new Exception('Password is too long. Maximum size is '.User::USER_LEN.' chars.');
			}
		}
		
		$password_info = password_get_info($password);
		if (empty($password_info['algo'])) {
			$this->password = $password;
			$this->password_hash($password);
		} else {
			$this->password = '';
			$this->password_hash = $password;
		}
	}
	
	public function password_hash($password = NULL) {
		if (empty($this->password_hash) && !empty($this->password)) {
			$this->password_hash = password_hash($this->password, PASSWORD_DEFAULT);
		}
		
		if (func_num_args() == 0) {
			if (empty($this->password_hash)) {
				throw new Exception('Password hash can not be empty');
			}
			return $this->password_hash;
		}
		
		$this->password_hash = password_hash($password, PASSWORD_DEFAULT);
	}
	
	public function password_verify($password) {
		$result = password_verify($password, $this->password_hash());
		
		if ($result) {
			$this->password = $password;
		}
		
		return $result;
	}
	
	public function isAdmin($isAdmin = NULL) {
		if (func_num_args() == 0) {
			return $this->isAdmin;
		}
		
		$this->isAdmin = (boolean)$isAdmin;
	}
	
	public function email($email = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->email;
		}
		
		if (!$force) {
			if (empty($email)) {
				throw new Exception('e-mail can not be empty');
			}
			
			if (
				!preg_match(
					'/^[A-Z_a-z0-9]+((\-|\.)?[A-Z_a-z0-9]+)*\@[A-Z_a-z0-9]+((\-|\.)?[A-Z_a-z]+[0-9]*)*\.[A-Z_a-z][A-Z_a-z]+$/',
					$email
				)
			) {
				throw new Exception('Invalid e-mail syntax');
			}
			
			$duplicate = UserManager::getByEMail($email);
			if ($duplicate && $duplicate->id() != $this->id()) {
				throw new Exception('Duplicated e-mail address');
			}
		}
		
		$this->email = $email;
	}
	
	public function newApiKey() {
		$count = 0;
		while (TRUE) {
			$key = random_str(32);
			
			// check for duplicate
			$user = UserManager::getByApiKey($key);
			if (empty($user)) {
				break;
			}
			
			if ($count++ > 3) {
				throw new Exception('API key already exists');
			}
		}
		
		$this->apiKey = $key;
		return $this->apiKey;
	}
	
	public function apiKey() {
		return $this->apiKey;
	}
	
	public function activate($activationCode) {
		if (Config::user('activationCode') != $activationCode) {
			throw new Exception('Invalid Activation Code');
		}
		
		Config::user('activationCode', NULL);
		Config::save();
	}
	
	public function __toString() {
		return '['.$this->id().'] '.$this->name();
	}	
}

class UserManager {
	static protected $users = array();
	
	/**
	 * Current user
	 * @var User
	 */
	static protected $current  = NULL;
	
	public static function isAdmin() {
		$current = self::current();
		
		if ($current === NULL) {
			return FALSE;
		}
		
		return $current->isAdmin();
	}
	
	public static function id() {
		$current = self::current();
		
		if ($current === NULL) {
			return -1;
		}
		
		return $current->id();
	}
	
	protected static function newUser($dbData) {
		if (empty($dbData)) {
			return NULL;
		}
		
		$newUser = NULL;
		foreach ($dbData as $user) {
			if (isset(self::$users[$user['id']])) {
				$newUser = self::$users[$user['id']];
				continue;
			}
			
			$newUser = new User(
				$user['id'],
				$user['name'],
				$user['password'],
				$user['isAdmin'] == '1',
				$user['email'],
				$user['apiKey']
			);
			
			self::$users[$user['id']] = $newUser;
		}
		
		return $newUser;
	}
	
	public static function get($id) {
		if (empty($id)) {
			return NULL;
		}
		
		if (isset(self::$users[$id])) {
			return self::$users[$id];
		}
		
		$users = DB::query(
			'SELECT * FROM "Users" WHERE "id" = :id',
			array(':id' => $id)
		)->fetchAll(PDO::FETCH_ASSOC);
		
		return self::newUser($users);
	}
	
	public static function getByName($name) {
		$users = DB::query(
			'SELECT * FROM "Users" WHERE "name" = :name OR "email" = :name',
			array(':name' => $name)
		)->fetchAll(PDO::FETCH_ASSOC);
		return self::newUser($users);
	}
	
	public static function getByEMail($email) {
		$users = DB::query(
			'SELECT * FROM "Users" WHERE "email" = :email',
			array(':email' => $email)
		)->fetchAll(PDO::FETCH_ASSOC);
		return self::newUser($users);
	}
	
	public static function getByApiKey($apiKey) {
		$users = DB::query(
			'SELECT * FROM "Users" WHERE "apiKey" = :apiKey',
			array(':apiKey' => $apiKey)
		)->fetchAll(PDO::FETCH_ASSOC);
		return self::newUser($users);
	}
	
	public static function getAll() {
		$users = DB::query(
			'SELECT * FROM "Users"',
			array()
		)->fetchAll(PDO::FETCH_ASSOC);
		self::newUser($users);
		return self::$users;
	}
	
	public static function countUsers() {
		$users = DB::query(
			'SELECT COUNT(*) cnt FROM "Users"',
			array()
		)->fetchAll(PDO::FETCH_ASSOC);
		return $users[0]['cnt'];
	}
	
	public static function save(User $user) {
		$user->validate();
		
		if ($user->id() < 0) {
			DB::query(
				'INSERT INTO "Users" ("name", "password", "isAdmin", "email", "apiKey") VALUES (:name, :password, :isAdmin, :email, :apiKey)',
				array(':name' => $user->name(), ':password' => $user->password_hash(), ':isAdmin' =>  (integer)$user->isAdmin(), ':email' => $user->email(), ':apiKey' => $user->apiKey())
			);
			return DB::lastInsertId('"Users_id_seq"');
		}
		
		DB::query(
			'UPDATE "Users"	SET "password" = :password, "isAdmin" = :isAdmin, "email" = :email, "apiKey" = :apiKey WHERE "Users"."id" = :id',
			array(':id' => $user->id(), ':password' => $user->password_hash(), ':isAdmin' => (integer)$user->isAdmin(), ':email' => $user->email(), ':apiKey' => $user->apiKey())
		);
		return $user->id();
	}
	
	public static function delete(User $user) {
		unset(self::$users[$user->id()]);
		DB::query(
			'DELETE FROM "Users" WHERE "Users"."id" = :id',
			array(':id' => $user->id())
		);
	}
	
	protected static function authenticate($name, $password) {
		if (empty($name) || empty($password)) {
			return FALSE;
		}
		
		$user = self::getByName($name);
		if (empty($user)) {
			return FALSE;
		}
		
		return $user->password_verify($password);
	}
	
	public static function current(User $user = NULL) {
		if (func_num_args() != 0) {
			self::$current = $user;
			if (empty($user)) {
				unset($_SESSION['user']);
			} else {
				Config::userInit($user->id());
				$_SESSION['user'] = $user->id();
			}
		}
		
		if (empty(self::$current)) {
			if (isset($_SESSION['user'])) {
				self::$current = self::get($_SESSION['user']);
			} else if (!empty($_COOKIE['user'])) {
				$cookie = $_COOKIE['user'];
				list(
					$name,
					$password,
				) = unserialize(base64_decode($cookie));
				self::login($name, $password);
			} else if (!empty($_SERVER['PHP_AUTH_USER'])) {
				self::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
			}
		}
		
		return self::$current;
	}
	
	public static function login($name, $password, $permanent= FALSE) {
		if (empty(self::$current)) {
			if (self::authenticate($name, $password)) {
				self::$current = self::getByName($name);
				if (empty(self::$current)) {
					return NULL;
				}
				$_SESSION['user'] = self::$current->id();
				if ($permanent) {
					$cookie = base64_encode(
						serialize(
							array(
								$name,
								$password,
							)
						)
					);
					setcookie(
						'user',                // name 
						$cookie,               // value
						time()+3600*24*365,    // 1 year
						'',                    // path
						'',                    // domain
						FALSE,                 // secure
						TRUE                   // http_only
					);
				}
			}
		}
		return self::$current;
	}
	
	public static function logout($error = NULL) {
		self::$current = NULL;
		unset($_SESSION['user']);
		unset($_COOKIE['user']);
		setcookie(
			'user',                // name 
			'',                    // value
			time()-3600*24         // yesterday
		);
		
		if (empty($error)) {
			session_destroy();
		} else {
			$_SESSION['error'] = $error;
		}
	}
	
}
