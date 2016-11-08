<?php 
if (empty($this->_parameters_)) {
	$nodeID = prmGET('nodeID');
	$selected = NULL;
	$required = NULL;
} else {
	list(
		$selected,
		$nodeID,
		$required
	) = array_pad($this->_parameters_, 3, NULL);
}
?>
<select name="propertyID" id="propertyID"<?php echo (empty($required) ? '' : ' class="required"'); ?>>
	<option></option>
	<?php foreach (PropertyManager::getAll($nodeID) as $property) { ?>
		<option value="<?php echo $property->id(); ?>"<?php echo ($selected == $property->id() ? ' selected="selected"' : ''); ?>><?php echo $property; ?></option>
	<?php } ?>
</select>
