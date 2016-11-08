<?php 
	list(
		$type,
		$parameter,
		$value
	) = array_pad($this->_parameters_, 3, NULL);
?>
<select name="<?php echo $type; ?>_params[<?php echo $parameter->Name; ?>]" <?php echo ($parameter->Required ? 'class="required"' : ''); ?>>
	<option value="">Relay</option>
	<option value="1" <?php echo ($value == 1 ? ' selected="selected"' : ''); ?>>Relay1</option>
	<option value="2" <?php echo ($value == 2 ? ' selected="selected"' : ''); ?>>Relay2</option>
</select>
