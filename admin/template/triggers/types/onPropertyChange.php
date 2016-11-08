<?php 
$selOperator = array(
	'===' => NULL,
	'!==' => NULL,
	'<'   => NULL,
	'<='  => NULL,
	'>'   => NULL,
	'>='  => NULL,
);

$trigger = TriggerManager::get(prmGET('id', $_SESSION['copyTrigger']));
if (empty($trigger) || $trigger->type() != 'onPropertyChange') {
	$nodeID = sessPOST('nodeID', -1);
	$propertyID = sessPOST('propertyID', -1);
	$value = sessPOST('value', '');
	
	$operator = sessPOST('select');
	if (!empty($operator)) {
		$selOperator[$operator] = ' selected="selected"';
	}
	
	$filter = FilterManager::getByName(sessPOST('filter'));
} else {
	$data = $trigger->data();
	
	$node = NodeManager::getByToken($data->node);
	$nodeID = empty($node) ? -1 : $node->id();
	
	$property = PropertyManager::getByName($data->device, $data->property);
	$propertyID = empty($property) ? -1 : $property->id();
	
	if (!empty($data->operator)) {
		$selOperator[$data->operator] = ' selected="selected"';
	}
	
	$filter = FilterManager::getByName($data->filter);
	$value = $data->value;
}

?>
<div id="trigger-parameters">
	<label for="nodeID">Node</label>          <?php echo $this->__call('/nodes/select', array($nodeID)); ?>
	<label for="propertyID">Property</label>  <?php echo $this->__call('/properties/select', array($propertyID, $nodeID, TRUE)); ?>
	<label for="operator">Operator</label>  
	<select name="operator" id="operator">
		<option value=""></option>
		<option value="==="<?php   echo $selOperator['===']; ?>>Equal</option>
		<option value="!=="<?php   echo $selOperator['!==']; ?>>Not Equal</option>
		<option value="&lt;"<?php  echo $selOperator['<'];   ?>>Less than</option>
		<option value="&lt;="<?php echo $selOperator['<='];  ?>>Less than or Equal</option>
		<option value="&gt;"<?php  echo $selOperator['>'];   ?>>Greater than</option>
		<option value="&gt;="<?php echo $selOperator['>='];  ?>>Greater than or Equal</option>
	</select>
	<label>Value</label> <input name="value" type="text" value="<?php echo htmlentities($value); ?>" />
	<fieldset class="group">
		<legend>Filter</legend>
		<label for="filter">Function</label> <?php echo $this->__call('/filters/select', array($filter, FALSE)); ?>
		<div id="filter-parameters">&nbsp;</div>
	</fieldset>
</div>
