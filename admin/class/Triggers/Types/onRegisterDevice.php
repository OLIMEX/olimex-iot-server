<?php 

class onRegisterDevice extends TriggerType {

	public static function validate($trigger, $data) {
		TriggerType::validate(
			$trigger,
			$data,
			array(
				'node',
				'deviceURL'
			)
		);
	}
	
	public static function toString($data) {
		return parent::nodeName($data).'.'.parent::deviceName($data);
	}
	
	public static function post() {
		return parent::post(
			array(
				'node',
				'device'
			)
		);
	}
}