<?php 

class everyHour extends TriggerType {

	public static function validate($trigger, $data) {
		TriggerType::validate(
			$trigger,
			$data,
			array(
				'minutes'
			)
		);
	}
	
	public static function toString($data) {
		return $data->minutes;
	}
	
	public static function post() {
		$data = new stdClass;
		$data->minutes = prmPOST('minutes', 0);
		return $data;
	}
}