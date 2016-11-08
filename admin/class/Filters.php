<?php 

class Filter {
	
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

class FilterManager {
	static protected $filters  = array();
	
	protected static function init() {
		if (!empty(self::$filters)) {
			return;
		}
		
		$filters = nodeGET('/api/filters');
		foreach ($filters as $filter) {
			self::$filters[] = new Filter($filter->Filter, $filter->Description, $filter->Parameters);
		}
	}
	
	public static function getByName($name) {
		if (empty($name)) {
			return NULL;
		}
		
		self::init();
		
		foreach (self::$filters as $filter) {
			if ($filter->name() == $name) {
				return $filter;
			}
		}
		
		return NULL;
	}
	
	public static function getAll() {
		self::init();
		return self::$filters;
	}
}