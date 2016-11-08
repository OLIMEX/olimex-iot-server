<?php 

class Node {
	const TOKEN_SIZE      = 16;
	
	protected $id         = NULL;
	protected $user       = NULL;
	protected $token      = '';
	protected $name       = '';
	
	protected $active     = FALSE;
	protected $ip         = NULL;
	protected $port       = NULL;
	protected $about      = NULL;
	
	protected $devices    = array();
	protected $devWorking = array();
	
	public function __construct($id = -1, User $user = NULL, $name = '', $token = '') {
		$this->id   = $id;
		$this->user = $user;
		$this->name = empty($user) ? NULL : $name;
		
		if ($id > 0) {
			$this->token = $token;
			$this->state();
		} else {
			if (UserManager::isAdmin() && !empty($token)) {
				$duplicate = NodeManager::getByToken($token, TRUE);
				if (empty($duplicate)) {
					$this->token = $token;
				} else {
					throw new Exception('Duplicated Node token');
				}
			} else if (isset($_SESSION['token'])) {
				$this->token = $_SESSION['token'];
			} else {
				$this->generateToken();
			}
		}
	}
	
	public function id() {
		return $this->id;
	}
	
	public function validate() {
		$user = UserManager::current();
		if (
			!(
				$user->id() == $this->userID() 
				||
				(UserManager::isAdmin() && $this->userID() == NULL)
			)
		) {
			throw new Exception('Invalid node user');
		}
		
		$this->token($this->token);
		$this->name($this->name);
	}
	
	public function userID() {
		if (empty($this->user)) {
			return NULL;
		}
		return $this->user->id();
	}
	
	protected function generateToken() {
		$count = 0;
		while (TRUE) {
			$token_size = Config::system('token.size');
			if (empty($token_size)) {
				$token_size = Node::TOKEN_SIZE;
			}
			
			$token = strtoupper(
				bin2hex(
					openssl_random_pseudo_bytes(
						ceil($token_size / 2)
					)
				)
			);
			
			$duplicate = NodeManager::getByToken($token);
			if (empty($duplicate)) {
				break;
			}
			
			if ($count++ > 3) {
				throw new Exception('Node token already exists');
			}
		}
		
		$this->token = $token;
		$_SESSION['token'] = $token;
	}
	
	public function token() {
		return $this->token;
	}
	
	public function name($name = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->name;
		}
		
		if (!$force) {
			if (empty($name) && $this->userID() != NULL) {
				throw new Exception('Node name can not be empty');
			}
			
			if (preg_match('/[^A-Za-z0-9_\-]/', $name)) {
				throw new Exception('Node name contains invalid characters');
			}
			
			if (!empty($name)) {
				$duplicate = NodeManager::getByName($name);
				if (!empty($duplicate) && $duplicate->id() != $this->id) {
					throw new Exception('Node name already exists');
				}
			}
		}
		
		$this->name = $name;
	}
	
	public function active() {
		return $this->active;
	}
	
	public function ip() {
		return $this->ip;
	}
	
	public function port() {
		return $this->port;
	}
	
	public function about() {
		return $this->about;
	}
	
	public function devices() {
		return $this->devices;
	}
	
	public function isWorking(Device $device) {
		return isset($this->devWorking[$device->id()]);
	}
	
	public function state() {
		$this->active     = FALSE;
		$this->ip         = NULL;
		$this->port       = NULL;
		$this->about      = NULL;
		
		$this->devices    = NodeManager::getDevices($this);
		$this->devWorking = array();
		
		$state = nodeGET('/api/nodes/'.$this->id(), $this->user);
		if (empty($state)) {
			return;
		}
		
		$index = count($state)-1;
		
		$this->active = TRUE;
		$this->ip     = $state[$index]->IP;
		$this->port   = $state[$index]->Port;
		$this->about  = $state[$index]->About;
		foreach ($state[$index]->Devices as $device) {
			$devWorking = DeviceManager::getByPath($device->URL);
			if (!isset($this->devices[$devWorking->id()])) {
				NodeManager::addDevice($this, $devWorking);
				$this->devices[$devWorking->id()] = $devWorking;
			}
			$this->devWorking[$devWorking->id()] = $devWorking;
		}
	}
	
	public function post($url, $data) {
		if (!$this->active) {
			throw new Exception('Can not POST to disconnected node');
		}
		
		return nodePOST(
			'/api/nodes/'.$this->id(),
			array(
				'URL'    => $url,
				'Method' => 'POST',
				'Data'   => $data
			),
			$this->user
		);
	}
	
	public function __toString() {
		return $this->name.' ['.$this->token.']';
	}
}

class NodeManager {
	static protected $nodes  = array();
	
	public static function canAdd() {
		return (
			(
				Config::system('nodes.unknown') == 'reject' &&
				UserManager::isAdmin()
			)
			||
			(
				Config::system('nodes.unknown') == 'user' && 
				Config::user('nodes.accept') == 'manual'
			)
		);
	}
	
	protected static function newNode($dbData) {
		if (empty($dbData)) {
			return NULL;
		}
		
		$newNode = NULL;
		foreach ($dbData as $node) {
			if (isset(self::$nodes[$node['id']])) {
				$newNode = self::$nodes[$node['id']];
				continue;
			}
			
			$newNode = new Node(
				$node['id'],
				UserManager::get($node['userID']),
				$node['name'],
				$node['token']
			);
			
			self::$nodes[$node['id']] = $newNode;
		}
		
		return $newNode;
	}
	
	public static function get($id) {
		if (empty($id)) {
			return NULL;
		}
		
		$user = UserManager::current();
		
		if (isset(self::$nodes[$id])) {
			if ($user->id() == self::$nodes[$id]->userID()) {
				return self::$nodes[$id];
			}
			return NULL;
		}
		
		$nodes = DB::query(
			'SELECT * FROM "Nodes" WHERE "id" = :id AND "userID" = :userID',
			array(':id' => $id, ':userID' => $user->id())
		)->fetchAll(PDO::FETCH_ASSOC);
		
		return self::newNode($nodes);
	}
	
	public static function getByName($name) {
		if (empty($name)) {
			return NULL;
		}
		
		$user = UserManager::current();
		
		$nodes = DB::query(
			'SELECT * FROM "Nodes" WHERE "name" = :name AND "userID" = :userID',
			array(':name' => $name, ':userID' => $user->id())
		)->fetchAll(PDO::FETCH_ASSOC);
		return self::newNode($nodes);
	}
	
	public static function getByToken($token, $all = FALSE) {
		$userID = UserManager::id();
		if ($all) {
			$nodes = DB::query(
				'SELECT * FROM "Nodes" WHERE "token" = :token',
				array(':token' => $token)
			)->fetchAll(PDO::FETCH_ASSOC);
		} else {
			$nodes = DB::query(
				'SELECT * FROM "Nodes" WHERE "token" = :token AND "userID" = :userID',
				array(':token' => $token, ':userID' => $userID)
			)->fetchAll(PDO::FETCH_ASSOC);
		}
		return self::newNode($nodes);
	}
	
	public static function getAll(User $user = NULL) {
		if (empty($user)) {
			$user = UserManager::current();
		}
		
		$nodes = DB::query(
			'SELECT * FROM "Nodes" WHERE "userID" = :userID',
			array(':userID' => $user->id())
		)->fetchAll(PDO::FETCH_ASSOC);
		self::newNode($nodes);
		
		$result = array();
		foreach (self::$nodes as $node) {
			if ($node->userID() == $user->id()) {
				$result[$node->id()] = $node;
			}
		}
		return $result;
	}
	
	public static function save(Node $node) {
		$node->validate();
		
		if ($node->id() < 0) {
			DB::query(
				'INSERT INTO "Nodes" ("userID", "token", "name") VALUES (:userID, :token, :name)',
				array(':userID' => $node->userID(), ':token' => $node->token(), ':name' => $node->name())
			);
			
			$_SESSION['token'] = NULL;
			unset($_SESSION['token']);
			
			return DB::lastInsertId('"Nodes_id_seq"');
		}
		
		DB::query(
			'UPDATE "Nodes"	SET "name" = :name, "token" = :token WHERE "Nodes"."id" = :id',
			array(':id' => $node->id(), ':name' => $node->name(), ':token' => $node->token())
		);
		return $node->id();
	}
	
	public static function delete(Node $node = NULL) {
		if (empty($node)) {
			throw new Exception('Invalid node');
		}
		
		$user = UserManager::current();
		
		unset(self::$nodes[$node->id()]);
		
		if (Config::system('nodes.unknown') == 'reject') {
			// unknown nodes are rejected so we do not delete the node
			// just clear node's name, user and events history
			DB::query(
				'UPDATE "Nodes" SET '.
					'"name" = NULL, '.
					'"userID" = NULL '.
				'WHERE "Nodes"."id" = :id AND "Nodes"."userID" = :userID',
				array(':id' => $node->id(), ':userID' => $user->id())
			);
			DB::query(
				'DELETE FROM "Events" WHERE "Events"."nodeID" = :nodeID',
				array(':nodeID' => $node->id())
			);
		} else {
			DB::query(
				'DELETE FROM "Nodes" WHERE "Nodes"."id" = :id AND "Nodes"."userID" = :userID',
				array(':id' => $node->id(), ':userID' => $user->id())
			);
		}
	}
	
	public static function addDevice(Node $node, Device $device) {
		if (empty($node)) {
			throw new Exception('Invalid node');
		}
		
		if (empty($device)) {
			throw new Exception('Invalid device');
		}
		
		DB::query(
			'INSERT INTO "NodeDevices" ("nodeID", "deviceID") VALUES (:nodeID, :deviceID)',
			array(':nodeID' => $node->id(), ':deviceID' => $device->id())
		);
	}
	
	public static function getDevices(Node $node) {
		if (empty($node)) {
			throw new Exception('Invalid node');
		}
		
		$deviceIDs = DB::query(
			'SELECT "deviceID" FROM "NodeDevices" WHERE "nodeID" = :nodeID',
			array(':nodeID' => $node->id())
		)->fetchAll(PDO::FETCH_ASSOC);
		
		$devices = array();
		foreach ($deviceIDs as $dbDevice) {
			$devices[$dbDevice['deviceID']] = DeviceManager::get($dbDevice['deviceID']);
		}
		
		return $devices;
	}
	public function newPassword(User $user, $old_password) {
		$nodes = self::getAll($user);
		foreach ($nodes as $node) {
			if (!$node->active()) {
				continue;
			}
			
			$node->post(
				'/config',
				array(
					'Config' => array(
						'Password' => $user->password()
					)
				)
			);
			
			$node->post(
				'/config/iot',
				array(
					'IoT' => array(
						'Password'  => $user->password()
					)
				)
			);
			
		}
	}
}