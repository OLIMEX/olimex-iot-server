<?php 

require_once 'everyHour.php';

class everyDay extends everyHour {

	public static function validate($trigger, $data) {
		TriggerType::validate(
			$trigger,
			$data,
			array(
				'minutes',
				'hour',
				'dowMask'
			)
		);
	}
	
	public static function toString($data) {
		return $data->hour.':'.$data->minutes;
	}
	
	public static function post() {
		$data = parent::post();
		
		$data->hour = prmPOST('hour', 0);
		
		$data->dowMask = 0;
		$dow = prmPOST('dow');
		if (is_array($dow)) {
			foreach ($dow as $d) {
				$data->dowMask += $d;
			}
		}
		
		return $data;
	}
}