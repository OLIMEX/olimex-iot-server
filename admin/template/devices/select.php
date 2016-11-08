<?php 
if (empty($this->_parameters_)) {
	$nodeID = prmGET('nodeID');
	$selected = -1;
	$disabled = FALSE;
} else {
	list(
		$selected, 
		$nodeID,
		$disabled
	) = array_pad($this->_parameters_, 3, NULL);
}
?>
<select name="deviceID" id="deviceID"<?php echo (empty($disabled) ? '' : ' disabled="disabled"');?>>
	<option value="0">All devices</option>
	<?php foreach (DeviceManager::getAll($nodeID) as $device) { ?>
		<option value="<?php echo $device->id()?>"<?php echo ($selected == $device->id() ? ' selected="selected"' : ''); ?>><?php echo $device->name()?></option>
	<?php } ?>
</select>
