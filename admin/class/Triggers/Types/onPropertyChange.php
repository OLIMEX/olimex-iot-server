<?php 

class onPropertyChange extends TriggerType {

	public static function validate($trigger, $data) {
		TriggerType::validate(
			$trigger,
			$data,
			array(
				'node',
				'device',
				'property',
				'operator',
				'value',
				'filter'
			)
		);
		
		if (empty($data->property)) {
			throw new Exception('Property can not be empty');
		}
		
		if (!empty($data->filter)) {
			$filter = FilterManager::getByName($data->filter);
			if ($filter == NULL) {
				throw new Exception('Invalid filter');
			}
			
			foreach ($filter->parameters() as $parameter) {
				if ($parameter->Required && $trigger->parameterValue('filter', $parameter->Name) == NULL) {
					throw new Exception('Filter parameter ['.$parameter->Name.'] can not be empty');
				}
			}
		}
	}
	
	public static function toString($data) {
		return parent::nodeName($data).'.'.parent::deviceName($data).'.'.$data->property;
	}
	
	public static function post() {
		return parent::post(
			array(
				'node',
				'property',
				'operator',
				'value',
				'filter'
			)
		);
	}
}