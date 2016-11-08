<?php 

require_once 'everyMonth.php';

class everyYear extends everyMonth {

	public static function validate($trigger, $data) {
		TriggerType::validate(
			$trigger,
			$data,
			array(
				'minutes',
				'hour',
				'date',
				'month'
			)
		);
	}
	
	public static function toString($data) {
		return $data->month.' '.$data->date.' '.$data->hour.':'.$data->minutes;
	}
	
	public static function post() {
		$data = parent::post();
		
		$data->month = prmPOST('month');
		
		return $data;
	}
}