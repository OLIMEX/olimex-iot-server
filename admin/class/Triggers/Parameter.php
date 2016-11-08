<?php 

class TriggerParameter {
	protected $id      = -1;
	protected $trigger = NULL;
	protected $type    = '';
	protected $name    = '';
	protected $value   = '';
	
	public function __construct($id = -1, Trigger $trigger = NULL, $type = '', $name = '', $value = '') {
		$this->id      = $id;
		$this->trigger = $trigger;
		$this->type    = $type;
		$this->name    = $name;
		$this->value   = $value;
	}
	
	public function id() {
		return $this->id;
	}
	
	public function trigger(Trigger $trigger = NULL) {
		if (func_num_args() == 0) {
			return $this->trigger;
		}
		
		$this->trigger = $trigger;
	}
	
	public function triggerID() {
		if (empty($this->trigger)) {
			return NULL;
		}
		return $this->trigger->id();
	}
	
	public function type($type = NULL) {
		if (func_num_args() == 0) {
			return $this->type;
		}
		$this->type = $type;
	}
	
	public function name($name = NULL) {
		if (func_num_args() == 0) {
			return $this->name;
		}
		$this->name = $name;
	}
	
	public function value($value = NULL) {
		if (func_num_args() == 0) {
			return $this->value;
		}
		$this->value = $value;
	}
	
	public function __clone() {
		$this->id = -1;
	}
}
