<?php 

class raw extends TriggerType {

	public static function validate($trigger, $data) {
		if (empty($data)) {
			throw new Exception('JSONPath can not be empty');
		}
		
		if (!is_string($data)) {
			throw new Exception('Wrong JSONPath type ['.gettype($data).']');
		}
	}
	
	public static function toString($data) {
		return $data;
	}
	
	public static function post() {
		return prmPOST('data');
	}
}