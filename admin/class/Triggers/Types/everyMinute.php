<?php 

class everyMinute extends TriggerType {

	public static function validate($trigger, $data) {
		return TRUE;
	}
	
	public static function toString($data) {
		return NULL;
	}
	
	public static function post() {
		return NULL;
	}
}