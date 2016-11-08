<?php 
	list(
		$selected,
		$disabled
	) = array_pad($this->_parameters_, 2, NULL);
?>
<select name="type" id="property-type"<?php echo (empty($disabled) ? '' : ' disabled="disabled"'); ?>>
	<option></option>
	<?php foreach (PropertyType::getAll() as $type) { ?>
		<option<?php echo ($selected == $type ? ' selected="selected"' : ''); ?>><?php echo $type; ?></option>
	<?php } ?>
</select>
