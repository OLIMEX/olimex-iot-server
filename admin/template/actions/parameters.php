<?php 
	$trigger = TriggerManager::get(prmGET('id', $_SESSION['copyTrigger']));
	$action = ActionManager::getByName(prmGET('name'));
	$action_params = sessPOST('action_params');
?>
<div id="action-parameters">
<?php if (empty($action) || count($action->parameters()) == 0) { ?>
	<p>Select action to be executed when the described event occurs.</p>
<?php } else { ?>
	<p><?php echo $action->description(); ?></p>
	<fieldset class="group">
		<legend>Action parameters</legend>
		<?php foreach ($action->parameters() as $parameter) { ?>
			<?php 
				$value = empty($action_params[$parameter->Name]) ? 
					(empty($trigger) ? 
						'' 
						: 
						$trigger->parameterValue('action', $parameter->Name)
					)
					:
					$action_params[$parameter->Name]
				;
			?>
			<label><?php echo $parameter->Name; ?></label> 
			<?php 
				$editor = (empty($parameter->Editor) ?
					'default'
					:
					$parameter->Editor
				);
				echo $this->__call('/triggers/parameters/'.$editor, array('action', $parameter, $value));
			?>
		<?php } ?>
	</fieldset>
<?php } ?>	
</div>
