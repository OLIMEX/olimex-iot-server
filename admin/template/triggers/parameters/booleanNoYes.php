<?php 
	list(
		$type,
		$parameter,
		$value
	) = array_pad($this->_parameters_, 3, NULL);
	
	$value = (is_bool($value) ?
		$value
		:
		(is_string($value) ?
			in_array(strtolower($value), array('true', 'on', '1'))
			:
			$value
		)
	);
	
	$selected = 'selected="selected"';
?>
<select name="<?php echo $type; ?>_params[<?php echo $parameter->Name; ?>]" <?php echo ($parameter->Required ? 'class="required"' : ''); ?>>
	<option value="false" <?php echo (empty($value) ? $selected : ''); ?>>No</option>
	<option value="true"  <?php echo (empty($value) ? '' : $selected); ?>>Yes</option>
</select>
