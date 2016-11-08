<?php 
if (empty($device)) { 
	throw new Exception('Invalid device', 400);
}

if (isset($_SESSION['post'])) {
	$device->name(sessPOST('name'), TRUE);
	$device->native(sessPOST('native'), TRUE);
	$device->eventsPath(sessPOST('eventsPath'), TRUE);
	$device->description(sessPOST('description'), TRUE);
}

$strDisabled = (empty($disabled) ? '' : 'disabled="disabled"');

$_SESSION['post'] = NULL;
unset($_SESSION['post']);
?>

<?php echo $this->backArrow(); ?>
<h1><?php echo $title; ?></h1>
<form method="POST" action="<?php echo $this->path(); ?>" class="container">
	<input type="hidden" name="id"  value="<?php echo $device->id(); ?>" />
	<label>Native</label>      <input type="checkbox" name="native"     value="1" <?php echo ($device->native() ? 'checked="checked"' : ''); ?> <?php echo $strDisabled;?> /><br/>
	<label>Name</label>        <input type="text"     name="name"       value="<?php echo htmlentities($device->name()); ?>"       <?php echo $strDisabled;?>/>
	<label>Events Path</label> <input type="text"     name="eventsPath" value="<?php echo htmlentities($device->eventsPath()); ?>" <?php echo $strDisabled;?>/>
	<label>Description</label> <textarea name="description" <?php echo $strDisabled;?>><?php echo htmlentities($device->description()); ?></textarea>
	<button type="submit"><?php echo $action; ?></button>
	<button type="button">Back</button>
</form>
