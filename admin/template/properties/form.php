<?php 
if (empty($property)) { 
	throw new Exception('Invalid property', 400);
}

if (isset($_SESSION['post'])) {
	$property->deviceID(sessPOST('deviceID'));
	$property->name(sessPOST('name'), TRUE);
	$property->type(sessPOST('type'), TRUE);
	$property->jsonPath(sessPOST('jsonPath'), TRUE);
	$property->description(sessPOST('description'), TRUE);
}

$strDisabled = (empty($disabled) ? '' : 'disabled="disabled"');

$_SESSION['post'] = NULL;
unset($_SESSION['post']);
?>

<?php echo $this->backArrow(); ?>
<h1><?php echo $title; ?></h1>
<form method="POST" action="<?php echo $this->path(); ?>" class="container">
	<input type="hidden" name="id"  value="<?php echo $property->id(); ?>" />
	<label>Device</label>      <?php echo $this->__call('/devices/select', array($property->deviceID(), $disabled)); ?>
	<label>Name</label>        <input type="text" name="name"     value="<?php echo htmlentities($property->name()); ?>"     <?php echo $strDisabled;?>/>
	<label>JSON Path</label>   <input type="text" name="jsonPath" value="<?php echo htmlentities($property->jsonPath()); ?>" <?php echo $strDisabled;?>/>

	<fieldset class="group">
		<legend>Data</legend>
		<label>Type</label>        <?php echo $this->__call('/properties/types/select', array($property->type(), $disabled)); ?>
		<label>Read Only</label>   <input type="checkbox" name="readOnly" value="1" <?php echo ($property->readOnly() ? 'checked="checked"' : ''); ?> <?php echo $strDisabled;?> />
		<label>Factor</label>      <input type="text" name="factor"   value="<?php echo htmlentities($property->factor()); ?>"   <?php echo $strDisabled;?>/>
		<label>Decimals</label>    <input type="text" name="decimals" value="<?php echo htmlentities($property->decimals()); ?>" <?php echo $strDisabled;?>/>
	</fieldset>
	
	<fieldset class="group">
		<legend>Visualisation</legend>
		<label>Label</label>       <input type="text" name="label"   value="<?php echo htmlentities($property->label()); ?>"   <?php echo $strDisabled;?>/>
		<label>Measure</label>     <input type="text" name="measure" value="<?php echo htmlentities($property->measure()); ?>" <?php echo $strDisabled;?>/>
		<label>Input Type</label>        
		<select name="inputType" <?php echo $strDisabled;?>>
			<option></option>
			<option <?php echo ($property->inputType() == 'text'     ? 'selected="selected"' : ''); ?>>text</option>
			<option <?php echo ($property->inputType() == 'checkbox' ? 'selected="selected"' : '');?>>checkbox</option>
		</select>
	</fieldset>
	
	<label>Description</label> <textarea name="description" <?php echo $strDisabled;?>><?php echo htmlentities($property->description()); ?></textarea>
	<button type="submit"><?php echo $action; ?></button>
	<button type="button">Back</button>
</form>
