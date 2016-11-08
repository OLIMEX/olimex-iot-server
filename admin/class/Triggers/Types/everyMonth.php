<?php 

require_once 'everyDay.php';

class everyMonth extends everyDay {

	public static function validate($trigger, $data) {
		TriggerType::validate(
			$trigger,
			$data,
			array(
				'minutes',
				'hour',
				'date'
			)
		);
	}
	
	public static function toString($data) {
		return $data->date.' '.$data->hour.':'.$data->minutes;
	}
	
	public static function post() {
		$data = parent::post();
		
		$data->date = prmPOST('date', 0);
		
		return $data;
	}
}