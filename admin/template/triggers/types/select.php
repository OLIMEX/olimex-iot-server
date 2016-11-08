<?php 
	list(
		$selected,
		$disabled
	) = array_pad($this->_parameters_, 2, NULL);
?>
<select name="type" id="trigger-type"<?php echo (empty($disabled) ? ' class="required"' : ' disabled="disabled"'); ?>>
	<option></option>
	<?php foreach (TriggerType::getAll() as $type) { ?>
		<option<?php echo ($selected == $type ? ' selected="selected"' : ''); ?>><?php echo $type; ?></option>
	<?php } ?>
</select>
