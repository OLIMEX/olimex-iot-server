<?php 

class Event {
	protected $id        = NULL;
	protected $timestamp = NULL;
	protected $node      = NULL;
	protected $data      = NULL;
	
	public function __construct($id = NULL, DateTime $timestamp = NULL, Node $node = NULL, $data = NULL) {
		$this->id        = $id;
		$this->timestamp = $timestamp;
		$this->node      = $node;
		$this->data      = $data;
	}
	
	public function id() {
		return $this->id;
	}
	
	public function timestamp() {
		return $this->timestamp;
	}
	
	public function node() {
		return $this->node;
	}
	
	public function nodeName() {
		return (empty($this->node) ?
			NULL
			:
			$this->node->name()
		);
	}
	
	public function device() {
		return (isset($this->data->EventURL) ?
			DeviceManager::getByPath($this->data->EventURL)
			:
			NULL
		);
	}
	
	public function deviceName() {
		$device = $this->device();
		return (empty($device) ?
			NULL
			:
			$device->name()
		);
	}
	
	public function data() {
		return $this->data;
	}
}

class EventsManager {
	
	protected static function newEvent($dbData) {
		if (empty($dbData)) {
			return array();
		}
		
		$newEvent = array();
		foreach ($dbData as $event) {
			$newEvent[] = new Event(
				$event['id'],
				new DateTime($event['timestamp']),
				NodeManager::get($event['nodeID']),
				json_decode($event['data'])
			);
		}
		
		return $newEvent;
	}
	
	public static function get($id) {
		if (empty($id)) {
			return NULL;
		}
		
		$events = DB::query(
			'SELECT * FROM "Events" WHERE "id" = :id',
			array(':id' => $id)
		)->fetchAll(PDO::FETCH_ASSOC);
		
		$events = self::newEvent($events);
		if (empty($events)) {
			return NULL;
		}
		return $events[0];
	}
	
	public static function getMany($ids) {
		if (empty($ids)) {
			return array();
		}
		
		$events = DB::query(
			'SELECT * FROM "Events" WHERE "id" = ANY(:ids::bigint[])',
			array(':ids' => '{'.$ids.'}')
		)->fetchAll(PDO::FETCH_ASSOC);
		
		return self::newEvent($events);
	}
	
	public function causedBy($id) {
		$events = DB::query(
			'SELECT '.
				'"Events".* '.
			'FROM "Events" '.
			'WHERE '.
				'("Events".data->'."'EventData'->>'ref'".')::bigint = :id '.
			'ORDER BY "Events"."timestamp"',
			array(':id' => $id)
		)->fetchAll(PDO::FETCH_ASSOC);
		
		return self::newEvent($events);
	}
}
