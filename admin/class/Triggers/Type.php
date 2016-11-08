<?php 

class TriggerType {
	
	protected static $types = array();
	
	protected static function init() {
		if (!empty(self::$types)) {
			return;
		}
		
		$types = DB::query(
			'SELECT * FROM "TriggerTypes"'
		)->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($types as $type) {
			if (class_exists($type['name']) && is_subclass_of($type['name'], 'TriggerType')) {
				self::$types[] = $type['name'];
			}
		}
	}
	
	public static function add($name) {
		if (empty($name)) {
			throw new Exception('Trigger type name can not be empty');
		}
		
		if (preg_match('/[^A-Za-z]/', $name)) {
			throw new Exception('Invalid trigger type name');
		}
		
		if (self::isValid($name)) {
			throw new Exception('Trigger type already exists');
		}
		
		if (!class_exists($name)) {
			throw new Exception('There is no implementation for this trigger type');
		}
		
		if (!is_subclass_of($name, 'TriggerType')) {
			throw new Exception($name.' must extends TriggerType');
		}
		
		DB::query(
			'INSERT INTO "TriggerTypes" ("name") VALUES (:name)',
			array(':name' => $name)
		);
		self::$types[] = $name;
	}
	
	public static function isValid($type) {
		self::init();
		return in_array($type, self::$types);
	}
	
	protected static function validate($trigger, $data, $properties = array()) {
		foreach ($properties as $property) {
			if (!property_exists($data, $property)) {
				throw new Exception('Missing '.$property);
			}
		}
	}
	
	protected static function post($properties) {
		$data = new stdClass;
		
		foreach ($properties as $p) {
			switch ($p) {
				case 'node': 
					$node = NodeManager::get(prmPOST('nodeID'));
					$data->node = empty($node) ? NULL : $node->token();
				break;
				
				case 'device':
					$device = DeviceManager::get(prmPOST('deviceID'));
					$data->deviceURL = empty($device) ? NULL : $device->eventsPath();
				break;
				
				case 'property':
					$property = PropertyManager::get(prmPOST('propertyID'));
					$data->device   = empty($property) ? NULL : $property->deviceName();
					$data->property = empty($property) ? NULL : $property->name();
				break;
				
				case 'operator':
					$data->operator = prmPOST('operator');
				break;
				
				case 'value':
					$data->value = prmPOST('value');
				break;
				
				case 'filter':
					$data->filter = prmPOST('filter');
				break;
			}
		}
		
		return $data;
	}
	
	public static function getAll() {
		self::init();
		return self::$types;
	}
	
	protected static function nodeName($data) {
		if (empty($data->node)) {
			return '*';
		}
		
		$node = NodeManager::getByToken($data->node);
		if (empty($node)) {
			return $data->node;
		}
		
		return $node->name();
	}
	
	protected static function deviceName($data) {
		if (empty($data->device)) {
			if (empty($data->deviceURL)) {
				return '';
			}
			
			$device = DeviceManager::getByPath($data->deviceURL);
			return (empty($device) ?
				NULL 
				: 
				$device->name()
			);
		}
		
		return $data->device;
	}
}
