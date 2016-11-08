<?php 
	list(
		$selected
	) = array_pad($this->_parameters_, 2, NULL);
?>
<select name="date" id="date">
	<?php for ($date=0; $date<32; $date++) { ?>
		<option<?php echo ($selected == $date ? ' selected="selected"' : ''); ?>><?php echo (empty($date) ? 'Last day' : $date); ?></option>
	<?php } ?>
</select>
