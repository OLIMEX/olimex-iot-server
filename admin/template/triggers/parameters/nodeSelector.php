<?php 
	list(
		$type,
		$parameter,
		$value
	) = array_pad($this->_parameters_, 3, NULL);
?>
<select name="<?php echo $type; ?>_params[<?php echo $parameter->Name; ?>]" <?php echo ($parameter->Required ? 'class="required"' : ''); ?>>
	<option value=""></option>
	<option value="[NodeToken]" <?php echo ($value == '[NodeToken]' ? ' selected="selected"' : ''); ?>>- Event Node -</option>
	<?php foreach (NodeManager::getAll() as $node) { ?>
		<option value="<?php echo $node->name(); ?>" <?php echo ($value == $node->name() ? ' selected="selected"' : ''); ?>><?php echo $node->name(); ?></option>
	<?php } ?>
</select>
