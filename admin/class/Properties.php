<?php 

class PropertyType {
	protected static $types = array();
	
	protected static function init() {
		if (!empty(self::$types)) {
			return;
		}
		
		$values = DB::query(
			'SELECT unnest(enum_range(NULL::"PropertyType")) as value'
		)->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($values as $v) {
			self::$types[] = $v['value'];
		}
	}
	
	public static function isValid($v) {
		self::init();
		return in_array($v, self::$types);
	}
	
	public static function getAll() {
		self::init();
		return self::$types;
	}
}

class Property {
	protected $id           = NULL;
	protected $device       = NULL;
	protected $name         = '';
	
	protected $type         = '';
	protected $readOnly     = TRUE;
	protected $factor       = NULL;
	protected $decimals     = NULL;
	
	protected $label        = NULL;
	protected $measure      = NULL;
	protected $inputType    = NULL;
	
	protected $jsonPath     = '';
	protected $description  = '';
	
	public function __construct(
		$id = -1, 
		Device $device = NULL, 
		$name = '', 
		
		$type = '', 
		$readOnly = TRUE,
		$factor = NULL,
		$decimals = NULL,
		
		$label = NULL,
		$measure = NULL,
		$inputType = NULL,
		
		$jsonPath = '', 
		$description = ''
	) {
		$this->id          = $id;
		$this->device      = $device;
		$this->name        = $name;
		
		$this->type        = $type;
		$this->readOnly    = $readOnly;
		$this->factor      = $factor;
		$this->decimals    = $decimals;
		
		$this->label       = $label;
		$this->measure     = $measure;
		$this->inputType   = $inputType;
		
		$this->jsonPath    = $jsonPath;
		$this->description = $description;
	}
	
	public function id() {
		return $this->id;
	}
	
	public function validate() {
		$this->name($this->name);
		$this->jsonPath($this->jsonPath);
	}
	
	public function device(Device $device = NULL) {
		if (func_num_args() == 0) {
			return $this->device;
		}
		
		$this->device = $device;
	}
	
	public function deviceID($deviceID = NULL) {
		if (func_num_args() == 0) {
			if (empty($this->device)) {
				return NULL;
			}
			return $this->device->id();
		}
		
		$this->device(DeviceManager::get($deviceID));
	}
	
	public function deviceName() {
		if (empty($this->device)) {
			return '*';
		}
		return ($this->device->native() ?
			DeviceManager::get(1)->name()
			:
			$this->device->name()
		);
	}
	
	public function native() {
		return (
			empty($this->device)
			||
			$this->device->native()
		);
	}
	
	public function name($name = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->name;
		}
		
		if (!$force) {
			if (empty($name)) {
				throw new Exception('Property name can not be empty');
			}
			
			if (preg_match('/[^A-Za-z0-9_]/', $name)) {
				throw new Exception('Property name contains invalid characters');
			}
		}
		
		$this->name = $name;
	}
	
	public function type($type = '', $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->type;
		}
		
		if (!$force) {
			if (empty($type)) {
				throw new Exception('Property type can not be empty');
			}
			
			if (!PropertyType::isValid($type)) {
				throw new Exception('Invalid property type');
			}
		}
		
		$this->type = $type;
	}
	
	public function readOnly($readOnly = FALSE, $force = FALSE) {
		if (func_num_args() == 0) {
			return (integer)$this->readOnly;
		}
		
		$this->readOnly = (boolean)$readOnly;
	}
	
	public function calcFactor() {
		return (empty($this->factor) ?
			1
			:
			$this->factor
		);
	}
	
	public function factor($factor = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->factor;
		}
		
		if (!$force) {
			if (!is_null($factor) && !is_numeric($factor)) {
				throw new Exception('Invalid factor');
			}
		}
		
		$this->factor = $factor;
	}
	
	public function decimals($decimals = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return (empty($this->decimals) ? 
				0 
				: 
				$this->decimals
			);
		}
		
		if (!$force) {
			if (!is_null($decimals) && !is_numeric($decimals)) {
				throw new Exception('Invalid decimals');
			}
		}
		
		$this->decimals = $decimals;
	}
	
	public function label($label = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return (empty($this->label) ?
				$this->name
				:
				$this->label
			);
		}
		
		if ($label == $this->name) {
			$label = NULL;
		}
		$this->label = $label;
	}
	
	public function measure($measure = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->measure;
		}
		$this->measure = $measure;
	}
	
	
	public function inputType($inputType = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->inputType;
		}
		
		if (!$force) {
			if (!is_null($inputType) && !in_array($inputType, array('text', 'checkbox'))) {
				throw new Exception('Invalid inputType');
			}
		}
		
		$this->inputType = $inputType;
	}
	
	public function jsonPath($jsonPath = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->jsonPath;
		}
		
		if (!$force) {
			if (empty($jsonPath)) {
				throw new Exception('Property jsonPath can not be empty');
			}
		}
		
		$this->jsonPath = $jsonPath;
	}
	
	public function description($description = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->description;
		}
		
		$this->description = $description;
	}
	
	public function isNumeric() {
		return in_array(	
			$this->type,
			array('binary', 'integer', 'float')
		);
	}
	
	public function parsePath() {
		$path = preg_split('/\./', $this->jsonPath);
		if (empty($path)) {
			throw new Exception('Can not parse JSON Path ['.$this.']');
		}

		if ($path[0] == '$') {
			array_shift($path);
		}
		
		foreach ($path as $p) {
			if (preg_match('/[^A-Za-z0-9_]/', $p)) {
				throw new Exception('Invalid JSON Path symbol ['.$this.'] ');
			}
		}
		
		$name = array_pop($path);
		
		return array(
			'path' => $path,
			'name' => $name,
		);
	}
	
	public function dataName() {
		$parsed = $this->parsePath();
		return $parsed['name'];
	}
	
	public function __toString() {
		return $this->deviceName().'.'.$this->name();
	}
}

class PropertyManager {
	static protected $properties  = array();
	
	protected static function newProperty($dbData) {
		if (empty($dbData)) {
			return NULL;
		}
		
		$newProperty = NULL;
		foreach ($dbData as $property) {
			if (isset(self::$properties[$property['id']])) {
				$newProperty = self::$properties[$property['id']];
				continue;
			}
			
			$newProperty = new Property(
				$property['id'],
				DeviceManager::get($property['deviceID']),
				$property['name'],
				
				$property['type'],
				$property['readOnly'],
				$property['factor'],
				$property['decimals'],
				
				$property['label'],
				$property['measure'],
				$property['inputType'],
				
				$property['jsonPath'],
				$property['description']
			);
			
			self::$properties[$property['id']] = $newProperty;
		}
		
		return $newProperty;
	}
	
	public static function get($id) {
		if (empty($id)) {
			return NULL;
		}
		
		if (isset(self::$properties[$id])) {
			return self::$properties[$id];
		}
		
		$properties = DB::query(
			'SELECT * FROM "Properties" WHERE "id" = :id',
			array(':id' => $id)
		)->fetchAll(PDO::FETCH_ASSOC);
		
		return self::newProperty($properties);
	}
	
	public static function getByName($device, $name) {
		if (empty($device) || empty($name)) {
			return NULL;
		}
		
		$user = UserManager::current();
		
		$properties = DB::query(
			'SELECT "Properties".* '.
			'FROM "Properties" '.
				'INNER JOIN "Devices" ON '.
					'"Devices"."id" = "Properties"."deviceID" '.
				'LEFT OUTER JOIN "Devices" AS "Native" ON '.
					'"Native"."id" = 1 AND "Devices"."native" '.
			'WHERE "Properties"."name" = :name AND ("Devices"."name" = :device OR "Native"."name" = :device)',
			array(':device' => $device, ':name' => $name)
		)->fetchAll(PDO::FETCH_ASSOC);
		return self::newProperty($properties);
	}
	
	public static function getByDevice(Device $device = NULL, $all_if_no_device = FALSE) {
		self::getAll();
		
		if (empty($device)) {
			if ($all_if_no_device) {
				return self::$properties;
			}
			return array();
		}
		
		return array_filter(
			self::$properties,
			function ($property) use ($device) {
				return (
					$device->id() == $property->deviceID()
					||
					($device->eventsPath() == '' && $property->native())
				);
			}
		);
	}
	
	public static function getAll($nodeID = NULL) {
		$properties = DB::query(
			'SELECT "Properties".* FROM "Properties" LEFT OUTER JOIN "Devices" ON "Devices"."id"="Properties"."deviceID" ORDER BY "Devices"."native" DESC, "Devices"."name", "Properties"."name"',
			array()
		)->fetchAll(PDO::FETCH_ASSOC);
		self::newProperty($properties);
		
		if (empty($nodeID) || $nodeID < 0) {
			return self::$properties;
		}
		
		$node = NodeManager::get($nodeID);
		if (empty($node)) {
			return array();
		}
		
		$devices = array();
		foreach ($node->devices() as $device) {
			$devices[] = $device->id();
		}
		
		return array_filter(
			self::$properties,
			function ($property) use ($devices) {
				$deviceID = $property->deviceID();
				return (
					empty($deviceID) || 
					in_array($deviceID, $devices)
				);
			}
		);
	}
	
	public static function save(Property $property) {
		$property->validate();
		
		if ($property->id() < 0) {
			DB::query(
				'INSERT INTO "Properties" '.
					'("deviceID", "name", "type", "readOnly", "factor", "decimals", "label", "measure", "inputType", "jsonPath", "description") '.
				'VALUES '.
					'(:deviceID, :name, :type, :readOnly, :factor, :decimals, :label, :measure, :inputType, :jsonPath, :description)',
				array(
					':deviceID' => $property->deviceID(), 
					':name' => $property->name(), 
					':type' => $property->type(), 
					':readOnly' => $property->readOnly(), 
					':factor' => $property->factor(), 
					':decimals' => $property->decimals(), 
					':label' => $property->label(), 
					':measure' => $property->measure(), 
					':inputType' => $property->inputType(), 
					':jsonPath' => $property->jsonPath(), 
					':description' => $property->description()
				)
			);
			return DB::lastInsertId('"Properties_id_seq"');
		}
		
		DB::query(
			'UPDATE "Properties" SET '.
				'"deviceID" = :deviceID, '.
				'"name" = :name, '.
				'"type" = :type, '.
				'"readOnly" = :readOnly, '.
				'"factor" = :factor, '.
				'"decimals" = :decimals, '.
				'"label" = :label, '.
				'"measure" = :measure, '.
				'"inputType" = :inputType, '.
				'"jsonPath" = :jsonPath, '.
				'"description" = :description '.
			'WHERE "Properties"."id" = :id',
			array(
				':id' => $property->id(), 
				':deviceID' => $property->deviceID(), 
				':name' => $property->name(), 
				':type' => $property->type(), 
				':readOnly' => $property->readOnly(), 
				':factor' => $property->factor(), 
				':decimals' => $property->decimals(), 
				':label' => $property->label(), 
				':measure' => $property->measure(), 
				':inputType' => $property->inputType(), 
				':jsonPath' => $property->jsonPath(), 
				':description' => $property->description()
			)
		);
		return $property->id();
	}
	
	public static function delete(Property $property = NULL) {
		if (empty($property)) {
			throw new Exception('Invalid property');
		}
		
		unset(self::$properties[$property->id()]);
		DB::query(
			'DELETE FROM "Properties" WHERE "Properties"."id" = :id',
			array(':id' => $property->id())
		);
	}
	
	public static function log(Node $node, Property $property, DateTime $date) {
		if (!$property->isNumeric()) {
			return array();
		}
		
		$parsed = $property->parsePath();
		
		$propertyType = $property->type() == 'float' ?
			'numeric'
			:
			'bigint'
		;
		
		$propertyField  = '"data"->';
		$propertySelect = "'".join("'->>'", array(join("'->'", $parsed['path']), $parsed['name']))."'";
		$propertyWhere  = "'".join("'->'",  array(join("'->'", $parsed['path']), $parsed['name']))."'";
		
		$commonWhere = 
			'"Events"."nodeID" = :nodeID AND '.
			'jsonb_typeof("Events".'.$propertyField.$propertyWhere.") != 'null'"
		;
		
		$timestamp = '"Events"."timestamp"'.(Config::user('timezone') == '' ?
			''
			:
			" AT TIME ZONE '".Config::user('timezone')."'"
		);
		
		$value = 'ROUND(("Events".'.$propertyField.$propertySelect.')::'.$propertyType.' / '.$property->calcFactor().', '.$property->decimals().')';
		
		$data = DB::query(
			'SELECT '.
				'"Events".id, '.
				$timestamp.' as x, '.
				$value.'  AS y, '.
				'"Events".data->'."'EventData'->>'ref'".' AS "causeLogID", '.
				'('.
					'SELECT array_to_json(array_agg(row_to_json(t))) '.
					'FROM ('.
						'SELECT e.id AS "triggerLogID" '.
						'FROM "Events" AS e '.
						'WHERE '.
							"(e.data->'Trigger'->>'ref')::bigint".' = "Events".id'.
					') AS t'.
				') AS triggers '.
			'FROM "Events" '.
			'WHERE '.$commonWhere.' AND '.$timestamp.' BETWEEN :day AND :day::date + INTERVAL '."'1 day' ".
			'ORDER BY "Events"."timestamp"',
			array(':nodeID' => $node->id(), ':day' => $date->format('Y-m-d'))
		)->fetchAll(PDO::FETCH_ASSOC);
		
		if (empty($data)) {
			return $data;
		}
		
		foreach ($data as &$d) {
			$d['y'] = 0+$d['y'];
			if (!empty($d['triggers'])) {
				$d['triggers'] = json_decode($d['triggers']);
			}
		}
		
		$format = 'Y-m-d H:i:s';
		
		$first = $data[0];
		if ($first['x'] > $date->format($format)) {
			
			$prev = $property->type() == 'binary' ? 
				DB::query(
					'SELECT '.$value.' AS y '.
					'FROM "Events" '.
					'WHERE '.$commonWhere.' AND '.$timestamp.' < :day '.
					'ORDER BY "Events"."timestamp" DESC '.
					'LIMIT 1',
					array(':nodeID' => $node->id(), ':day' => $date->format('Y-m-d'))
				)->fetchAll(PDO::FETCH_ASSOC)
				:
				array()
			;
			
			$first['id'] = -1;
			$first['x'] = $date->format($format);
			if (!empty($prev)) {
				$first['y'] = $prev[0]['y'];
			}
			$first['causeLogID'] = NULL;
			$first['triggers'] = NULL;
			array_unshift($data, $first);
		}
		
		$last = array_pop($data);
		array_push($data, $last);
		
		if ($date->format('Y-m-d') == date('Y-m-d')) {
			$now = new DateTime('now', new DateTimeZone('Europe/Sofia'));
			$last['x'] = $now->format($format);
		} else {
			$last['x'] = modifyDate($date, '+1 day -1 second')->format($format);
		}
		$last['id'] = -2;
		$last['causeLogID'] = NULL;
		$last['triggers'] = NULL;
		array_push($data, $last);
		
		return $data;
	}
}