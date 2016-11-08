<?php 

class Action {
	
	protected $name        = '';
	protected $description = '';
	protected $parameters  = array();
	
	public function __construct($name = '', $description, $parameters = array()) {
		$this->name = $name;
		$this->description = $description;
		$this->parameters = $parameters;
	}
	
	public function name() {
		return $this->name;
	}
	
	public function description() {
		return $this->description;
	}
	
	public function parameters() {
		return $this->parameters;
	}
	
	public function __toString() {
		return $this->name;
	}
}

class ActionManager {
	static protected $actions  = array();
	
	protected static function init() {
		if (!empty(self::$actions)) {
			return;
		}
		
		$actions = nodeGET('/api/actions');
		foreach ($actions as $action) {
			self::$actions[] = new Action($action->Action, $action->Description, $action->Parameters);
		}
	}
	
	public static function getByName($name) {
		self::init();
		
		foreach (self::$actions as $action) {
			if ($action->name() == $name) {
				return $action;
			}
		}
		
		return NULL;
	}
	
	public static function getAll() {
		self::init();
		return self::$actions;
	}
}