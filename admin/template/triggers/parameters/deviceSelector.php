<?php 
	list(
		$type,
		$parameter,
		$value
	) = array_pad($this->_parameters_, 3, NULL);
?>
<select name="<?php echo $type; ?>_params[<?php echo $parameter->Name; ?>]" <?php echo ($parameter->Required ? 'class="required"' : ''); ?>>
	<option value=""></option>
	<option value="[Device]" <?php echo ($value == '[Device]' ? ' selected="selected"' : ''); ?>>- Event Device -</option>
	<option value="[RegisteredDevice]" <?php echo ($value == '[RegisteredDevice]' ? ' selected="selected"' : ''); ?>>- Registered Device -</option>
	<?php foreach (DeviceManager::getAll() as $device) { ?>
		<option value="<?php echo $device->name(); ?>" <?php echo ($value == $device->name() ? ' selected="selected"' : ''); ?>><?php echo $device->name(); ?></option>
	<?php } ?>
</select>
