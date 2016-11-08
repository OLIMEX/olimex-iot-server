<?php 
list(
	$node,
	$device,
	$property,
	$class
) = array_pad($this->_parameters_, 4, NULL);

if (is_string($property)) {
	$property = PropertyManager::getByName($device->name(), $property);
}

if (empty($node) || empty($device) || empty($property)) {
	return;
}

$inputType = $property->inputType();
if (empty($inputType)) {
	return;
}

if (empty($class)) {
	$class = array();
} else {
	$class = (array)$class;
}

if ($property->isNumeric()) {
	$class[] = 'number';
}

?>

<label>
	<?php echo $property->label(); ?>
	<?php if ($property->measure()) { ?>
		[<?php echo $property->measure(); ?>]
	<?php } ?>
</label>

<input 
	type="<?php echo $inputType; ?>" 
	name="<?php echo $property->dataName(); ?>" 
	<?php if ($inputType == 'checkbox') { ?>
		value="<?php echo $property->calcFactor(); ?>"
	<?php } ?>
	<?php echo (empty($class) ? '' : 'class="'.join(' ', $class).'"'); ?>
	<?php if ($property->isNumeric()) { ?>
		data-factor="<?php echo $property->calcFactor(); ?>"
		data-decimals="<?php echo $property->decimals(); ?>"
	<?php } ?>
	<?php if ($property->readOnly()) { ?>
		disabled="disabled"
	<?php } ?>
/>

<?php if ($property->isNumeric()) { ?>
	<a title="View <?php echo $property->label(); ?> Graph" href="/svg/property?node=<?php echo $node->name(); ?>&device=<?php echo $device->name(); ?>&property=<?php echo $property->name(); ?>">
		<img src="/images/graph.png" class="graph" />
	</a>
<?php } ?>
