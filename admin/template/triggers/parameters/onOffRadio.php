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
	
	$checked = 'checked="checked"';
?>
<div class="radio">
	<label>Off</label><input name="<?php echo $type; ?>_params[<?php echo $parameter->Name; ?>]" type="radio" value="0" <?php echo (empty($value) ? $checked : ''); ?>/>
	<label>On</label> <input name="<?php echo $type; ?>_params[<?php echo $parameter->Name; ?>]" type="radio" value="1" <?php echo (empty($value) ? '' : $checked); ?>/>
</div>