<?php 
	list(
		$selected
	) = array_pad($this->_parameters_, 1, NULL);
?>
<select name="hour" id="hour">
	<?php for ($hour=0; $hour<24; $hour++) { ?>
		<option<?php echo ($selected == $hour ? ' selected="selected"' : ''); ?>><?php echo $hour; ?></option>
	<?php } ?>
</select>
