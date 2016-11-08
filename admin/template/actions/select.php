<?php 
	list(
		$selected,
		$disabled
	) = array_pad($this->_parameters_, 2, NULL);
?>
<select name="action" id="action"<?php echo (empty($disabled) ? ' class="required"' : ' disabled="disabled"'); ?>>
	<option></option>
	<?php foreach (ActionManager::getAll() as $action) { ?>
		<option<?php echo ($selected == $action->name() ? ' selected="selected"' : ''); ?>><?php echo $action->name(); ?></option>
	<?php } ?>
</select>
