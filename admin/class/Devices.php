<?php 

class Device {
	protected $id          = NULL;
	protected $name        = NULL;
	protected $native      = FALSE;
	protected $eventsPath  = '';
	protected $description = '';
	
	public function __construct($id = -1, $name = '', $native = FALSE, $eventsPath = '', $description = '') {
		$this->id          = $id;
		$this->name        = $name;
		$this->native      = $native;
		$this->eventsPath  = $eventsPath;
		$this->description = $description;
	}
	
	public function id() {
		return $this->id;
	}
	
	public function validate() {
		$this->name($this->name);
		$this->eventsPath($this->eventsPath);
	}
	
	public function name($name = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->name;
		}
		
		if (!$force) {
			if (empty($name)) {
				throw new Exception('Device name can not be empty');
			}
			
			if (preg_match('/[^A-Za-z0-9_\-]/', $name)) {
				throw new Exception('Device name contains invalid characters');
			}
			
			$duplicate = DeviceManager::getByName($name);
			if (!empty($duplicate) && $duplicate->id() != $this->id) {
				throw new Exception('Device name already exists');
			}
		}
		
		$this->name = $name;
	}
	
	public function native($native = FALSE, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->native;
		}
		
		$this->native = $native;
	}
	
	public function eventsPath($eventsPath = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->eventsPath;
		}
		
		if (!$force) {
			if (preg_match('/[^A-Za-z0-9_\-\/]/', $eventsPath)) {
				throw new Exception('Device eventsPath contains invalid characters');
			}
			
			if (!empty($eventsPath)) {
				$duplicate = DeviceManager::getByPath($eventsPath);
				if (!empty($duplicate) && $duplicate->id() != $this->id) {
					throw new Exception('Device eventsPath already exists');
				}
			}
		}
		
		$this->eventsPath = $eventsPath;
	}
	
	public function description($description = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->description;
		}
		
		$this->description = $description;
	}
	
	public function __toString() {
		return $this->name;
	}
}

class DeviceManager {
	static protected $devices = array();
	
	protected static function newDevice($dbData) {
		if (empty($dbData)) {
			return NULL;
		}
		
		$newDevice = NULL;
		foreach ($dbData as $device) {
			if (isset(self::$devices[$device['id']])) {
				$newDevice = self::$devices[$device['id']];
				continue;
			}
			
			$newDevice = new Device(
				$device['id'],
				$device['name'],
				$device['native'],
				$device['eventsPath'],
				$device['description']
			);
			
			self::$devices[$device['id']] = $newDevice;
		}
		
		return $newDevice;
	}
	
	public static function getByName($name) {
		$devices = DB::query(
			'SELECT * FROM "Devices" WHERE "name" = :name',
			array(':name' => $name)
		)->fetchAll(PDO::FETCH_ASSOC);
		return self::newDevice($devices);
	}
	
	public static function getByPath($path) {
		if (empty($path)) {
			return NULL;
		}
		
		$devices = DB::query(
			'SELECT * FROM "Devices" WHERE "eventsPath" = :path',
			array(':path' => $path)
		)->fetchAll(PDO::FETCH_ASSOC);
		return self::newDevice($devices);
	}
	
	public static function get($id) {
		if (empty($id)) {
			return NULL;
		}
		
		if (isset(self::$devices[$id])) {
			return self::$devices[$id];
		}
		
		$devices = DB::query(
			'SELECT * FROM "Devices" WHERE "id" = :id',
			array(':id' => $id)
		)->fetchAll(PDO::FETCH_ASSOC);
		
		return self::newDevice($devices);
	}
	
	public static function getAll($nodeID = NULL) {
		$devices = DB::query(
			'SELECT * FROM "Devices" ORDER BY "native" DESC, "name"',
			array()
		)->fetchAll(PDO::FETCH_ASSOC);
		self::newDevice($devices);
		
		if (empty($nodeID) || $nodeID < 0) {
			return self::$devices;
		}
		
		$node = NodeManager::get($nodeID);
		if (empty($node)) {
			return array();
		}
		
		$devices = array();
		foreach ($node->devices() as $device) {
			$devices[$device->id()] = $device;
		}
		
		return $devices;
	}

	public static function save(Device $device) {
		$device->validate();
		
		if ($device->id() < 0) {
			DB::query(
				'INSERT INTO "Devices" ("name", "native", "eventsPath", "description") VALUES (:name, :native, :eventsPath, :description)',
				array(':name' => $device->name(), ':native' => $device->native(), ':eventsPath' => $device->eventsPath(), ':description' => $device->description())
			);
			return DB::lastInsertId('"Devices_id_seq"');
		}
		
		DB::query(
			'UPDATE "Devices" SET "name" = :name, "native" = :native, "eventsPath" = :eventsPath, "description" = :description WHERE "Devices"."id" = :id',
			array(':id' => $device->id(), ':name' => $device->name(), ':native' => $device->native(), ':eventsPath' => $device->eventsPath(), ':description' => $device->description())
		);
		return $device->id();
	}
	
	public static function delete(Device $device = NULL) {
		if (empty($device)) {
			throw new Exception('Invalid device');
		}
		
		unset(self::$devices[$device->id()]);
		DB::query(
			'DELETE FROM "Devices" WHERE "Devices"."id" = :id',
			array(':id' => $device->id())
		);
	}
}
