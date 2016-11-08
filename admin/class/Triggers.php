<?php 

class Trigger {
	protected $id         = NULL;
	protected $user       = NULL;
	protected $active     = FALSE;
	protected $type       = '';
	protected $data       = NULL;
	protected $action     = '';
	protected $parameters = array();
	
	public function __construct($id = -1, User $user = NULL, $active = FALSE, $type = '', $data = NULL, $action = '') {
		$this->id = $id;
		
		if ($id > 0) {
			$this->user = $user;
		} else {
			$this->user($user);
		}
		
        $this->active     = $active;
        $this->type       = $type;
        $this->data       = $data;
        $this->action     = $action;
		$this->parameters = array();
	}
	
	public function id() {
		return $this->id;
	}
	
	public function editable() {
		$user = UserManager::current();
		return (
			$this->id > 0 &&
			(
				$user->isAdmin() || 
				$user->id() == $this->userID()
			)
		);
	}
	
	public function validate() {
		$user = UserManager::current();
		if ($user->id() != $this->userID() && !$user->isAdmin()) {
			throw new Exception('Invalid trigger user');
		}
		
		$this->type($this->type);
		$this->data($this->data);
		$this->action($this->action);
	}
	
	public function userID() {
		if (empty($this->user)) {
			return NULL;
		}
		return $this->user->id();
	}
	
	public function user(User $user = NULL) {
		if (func_num_args() == 0) {
			return $this->user;
		}
		
		$currentUser = UserManager::current();
		
		$this->user = $currentUser->isAdmin() ?
			(empty($user) ?
				$user
				:
				$currentUser
			)
			:
			$currentUser
		;
	}
	
	public function active($active = FALSE) {
		if (func_num_args() == 0) {
			return $this->active;
		}
		$this->active = $active;
	}
	
	public function type($type = '', $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->type;
		}
		
		if (!$force) {
			if (empty($type)) {
				throw new Exception('Trigger type can not be empty');
			}
			
			if (!TriggerType::isValid($type)) {
				throw new Exception('Invalid trigger type');
			}
		}
		
		$this->type = $type;
	}
	
	public function data($data = NULL, $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->data;
		}
		
		if (!$force) {
			if (!empty($this->type)) {
				call_user_func(array($this->type, 'validate'), $this, $data);
			}
		}
		
		$this->data = $data;
	}
	
	public function dataToString() {
		if (empty($this->type)) {
			return NULL;
		}
		return call_user_func(array($this->type, 'toString'), $this->data);
	}
	
	public function action($action = '', $force = FALSE) {
		if (func_num_args() == 0) {
			return $this->action;
		}
		
		if (!$force) {
			$objAction = ActionManager::getByName($action);
			if ($objAction == NULL) {
				throw new Exception('Invalid trigger action');
			}
			
			foreach ($objAction->parameters() as $parameter) {
				if ($parameter->Required && $this->parameterValue('action', $parameter->Name) == NULL) {
					throw new Exception('Action parameter ['.$parameter->Name.'] can not be empty');
				}
			}
		}
		
		$this->action = $action;
	}
	
	public function addParameter(TriggerParameter $parameter) {
		$this->parameters[] = $parameter;
	}
	
	public function parameterValue($type, $name) {
		foreach ($this->parameters as $parameter) {
			if ($parameter->type() == $type && $parameter->name() == $name) {
				return $parameter->value();
			}
		}
		return NULL;
	}
	
	public function clearParameters() {
		$this->parameters = array();
	}
	
	public function parameters($type = NULL) {
		if (func_num_args() == 0) {
			return $this->parameters;
		}
		
		$result = array();
		foreach ($this->parameters as $parameter) {
			if ($parameter->type() == $type) {
				$result[] = $parameter;
			}
		}
		return $result;
	}
	
	public function __clone() {
		$this->id = -1;
		
		foreach ($this->parameters as $i => $p) {
			$this->parameters[$i] = clone $p;
			$this->parameters[$i]->trigger($this);
		}
	}
	
	public function __toString() {
		return $this->type().' '.$this->dataToString().' call '.$this->action();
	}
}

class TriggerManager {
	static protected $triggers = array();
	
	protected static function newTrigger($apiData) {
		if (empty($apiData)) {
			return NULL;
		}
		
		$newTrigger = NULL;
		foreach ($apiData as $trigger) {
			if (isset(self::$triggers[$trigger->ID])) {
				$newTrigger = self::$triggers[$trigger->ID];
				continue;
			}
			
			if (empty($trigger->ID)) {
				$id = empty(self::$triggers) ? 
					0
					:
					min(array_keys(self::$triggers))
				;
				
				$trigger->ID = $id < 0 ?
					$id - 1
					:
					-1
				;
			}
			
			$newTrigger = new Trigger(
				$trigger->ID,
				UserManager::get($trigger->UserID),
				$trigger->Active,
				$trigger->Type,
				$trigger->Data,
				$trigger->Action
			);
			
			foreach ($trigger->Parameters as $parameter) {
				$newTrigger->addParameter(
					new TriggerParameter(
						$parameter->id,
						$newTrigger,
						'action',
						$parameter->name,
						$parameter->value
					)
				);
			}
			
			foreach ($trigger->Filter as $parameter) {
				$newTrigger->addParameter(
					new TriggerParameter(
						$parameter->id,
						$newTrigger,
						'filter',
						$parameter->name,
						$parameter->value
					)
				);
			}
			
			self::$triggers[$trigger->ID] = $newTrigger;
		}
		
		return $newTrigger;
	}
	
	public static function get($id) {
		if (empty($id) || $id < 0) {
			return NULL;
		}
		
		if (empty(self::$triggers)) {
			self::getAll();
		}
		
		$user = UserManager::current();
		
		if (isset(self::$triggers[$id])) {
			$trigger = self::$triggers[$id];
			if (
				$trigger->userID() == NULL ||
				$trigger->userID() == $user->id()
			) {
				return $trigger;
			}
		}
		
		return NULL;
	}
	
	public static function getAll() {
		$user = UserManager::current();
		
		$triggers = nodeGET('/api/triggers');
		self::newTrigger($triggers);
		
		$result = array();
		foreach (self::$triggers as $trigger) {
			if (
				$trigger->userID() == NULL ||
				$trigger->userID() == $user->id()
			) {
				$result[$trigger->id()] = $trigger;
			}
		}
		return $result;
	}
	
	protected static function saveParameters($triggerID, $parameters) {
		DB::query(
			'DELETE FROM "Parameters" WHERE "triggerID" = :triggerID',
			array(':triggerID' => $triggerID)
		);
		
		foreach ($parameters as $parameter) {
			$value = $parameter->value();
			if (empty($value) && $value !== '0') {
				continue;
			}
			
			DB::query(
				'INSERT INTO "Parameters" ("triggerID", "type", "name", "value") VALUES (:triggerID, :type, :name, :value)',
				array(':triggerID' => $triggerID, ':type' => $parameter->type(), ':name' => $parameter->name(), ':value' => $value)
			);
		}
	}
	
	public static function save(Trigger $trigger) {
		$trigger->validate();
		
		if ($trigger->id() < 0) {
			DB::query(
				'INSERT INTO "Triggers" ("userID", "active", "type", "data", "action") VALUES (:userID, :active, :type, :data, :action)',
				array(':userID' => $trigger->userID(), ':active' => $trigger->active(), ':type' => $trigger->type(), ':data' => json_encode($trigger->data()), ':action' => $trigger->action())
			);
			$triggerID = DB::lastInsertId('"Triggers_id_seq"');
			
			self::saveParameters($triggerID, $trigger->parameters());
			return $triggerID;
		}
		
		DB::query(
			'UPDATE "Triggers"	SET "userID" = :userID, "active" = :active, "data" = :data, "action" = :action WHERE "Triggers"."id" = :id',
			array(':id' => $trigger->id(), ':userID' => $trigger->userID(), ':active' => $trigger->active(), ':data' =>json_encode($trigger->data()), ':action' => $trigger->action())
		);
		self::saveParameters($trigger->id(), $trigger->parameters());
		
		return $trigger->id();
	}
	
	public static function delete(Trigger $trigger = NULL) {
		if (empty($trigger)) {
			throw new Exception('Invalid trigger');
		}
		
		$user = UserManager::current();
		if (
			($user->isAdmin() && $trigger->userID() == NULL) || 
			$user->id() == $trigger->userID()
		) {
			unset(self::$triggers[$trigger->id()]);
			DB::query(
				'DELETE FROM "Triggers" WHERE "Triggers"."id" = :id',
				array(':id' => $trigger->id())
			);
		}
	}
	
	public function log(Trigger $trigger) {
		$dbLog = DB::query(
			'SELECT '.
				'"Events".*, '.
				"to_char(timestamp, 'DD Mon YYYY HH24:MI:SS TZ') as timestamp ".
			'FROM "Events" '.
			"WHERE (data->'Trigger'->>'TriggerID')::int = :triggerID ".
			'ORDER BY "Events".timestamp DESC LIMIT 15',
			array(':triggerID' => $trigger->id())
		);
		
		$log = array();
		foreach ($dbLog as $l) {
			$l['data'] = @json_decode($l['data']);
			
			if (isset($l['data']->Trigger->Action)) {
				$action = ActionManager::getByName($l['data']->Trigger->Action);
				
				if (isset($l['data']->Trigger->Parameters)) {
					$parameters = $l['data']->Trigger->Parameters;
					$l['data']->Trigger->Parameters = new stdClass();
					
					foreach ($action->parameters() as $p) {
						if (isset($parameters->{$p->Name})) {
							$l['data']->Trigger->Parameters->{$p->Name} = $parameters->{$p->Name};
						}
					}
				}
			}
			
			$log[] = $l;
		}
		
		return $log;
	}
	
}
