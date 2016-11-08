<?php 
	list(
		$selected,
		$disabled
	) = array_pad($this->_parameters_, 2, NULL);
?>
<select name="filter" id="filter"<?php echo (empty($disabled) ? '' : ' disabled="disabled"'); ?>>
	<option></option>
	<?php foreach (FilterManager::getAll() as $filter) { ?>
		<option<?php echo ($selected == $filter->name() ? ' selected="selected"' : ''); ?>><?php echo $filter->name(); ?></option>
	<?php } ?>
</select>

