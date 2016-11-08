<?php 
list(
	$selected
) = array_pad($this->_parameters_, 1, NULL);
?>
<select name="nodeID" id="nodeID">
	<option value="-1">All nodes</option>
	<?php foreach (NodeManager::getAll() as $node) { ?>
		<option value="<?php echo $node->id(); ?>" <?php echo ($selected == $node->id() ? ' selected="selected"' : ''); ?>><?php echo $node->name(); ?></option>
	<?php } ?>
</select>
