<?php 
	list(
		$selected
	) = array_pad($this->_parameters_, 1, NULL);
?>
<select name="minutes" id="minutes">
	<?php for ($minutes=0; $minutes<60; $minutes++) { ?>
		<option<?php echo ($selected == $minutes ? ' selected="selected"' : ''); ?>><?php echo $minutes; ?></option>
	<?php } ?>
</select>
