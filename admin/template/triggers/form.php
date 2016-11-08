<?php 

$_SESSION['copyTrigger'] = prmGET('copy');

if (isset($_SESSION['post'])) {
	$trigger->type(sessPOST('type'), TRUE);
	$trigger->action(sessPOST('action'), TRUE);
}

$user = UserManager::current();
$strDisabled = $disabled ? 'disabled' : '';
?>
<script src="<?php echo $this->version('/scripts/triggers.js'); ?>"></script>
<?php echo $this->backArrow(); ?>
<h1><?php echo $title; ?></h1>
<form method="POST" action="<?php echo $this->path(); ?>" class="container <?php echo $strDisabled;?>">
	<input type="hidden" name="id" id="triggerID" value="<?php echo $trigger->id(); ?>" />
	
	<?php if ($user->isAdmin()) { ?>
		<label for="userID">User</label> 
		<select name="userID" id="userID">
			<option value="" <?php echo ($trigger->userID() == NULL ? 'selected="selected"' : '')?>>All users</option>
			<option value="<?php echo $user->id(); ?>" <?php echo ($trigger->userID() == $user->id() ? 'selected="selected"' : '')?>>Only me</option>
		</select>
	<?php } ?>
	
	<label for="trigger-active">Active</label> <input type="checkbox" id="trigger-active" name="active" value="1" <?php echo ($trigger->active() || $trigger->id() < 0 ? 'checked="checked"' : ''); ?>/><br/>
	
	<fieldset class="group">
		<legend>Condition</legend>
		<label for="trigger-type">Type</label> <?php echo $this->__call('/triggers/types/select', array($trigger->type(), $disabled)); ?>
		<div id="trigger-parameters">&nbsp;</div>
	</fieldset>
	
	<fieldset class="group">
		<legend>Action</legend>
		<label for="action">Function</label> <?php echo $this->__call('/actions/select', array($trigger->action(), FALSE)); ?>
		<div id="action-parameters">&nbsp;</div>
	</fieldset>
	
	<button type="submit"><?php echo $action ?></button>
	<button type="button">Back</button>
</form>
