<?php 
	list(
		$type,
		$parameter,
		$value
	) = array_pad($this->_parameters_, 3, NULL);
?>
<input name="<?php echo $type; ?>_params[<?php echo $parameter->Name; ?>]" type="text" value="<?php echo htmlentities($value); ?>" <?php echo ($parameter->Required ? 'class="required"' : ''); ?>/>
