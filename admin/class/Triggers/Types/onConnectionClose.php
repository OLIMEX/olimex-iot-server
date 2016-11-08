<?php 

class onConnectionClose extends TriggerType {

	public static function validate($trigger, $data) {
		TriggerType::validate(
			$trigger,
			$data,
			array(
				'node'
			)
		);
	}
	
	public static function toString($data) {
		return parent::nodeName($data);
	}
	
	public static function post() {
		return parent::post(
			array(
				'node'
			)
		);
	}
}