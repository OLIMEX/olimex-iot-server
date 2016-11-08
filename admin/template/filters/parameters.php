<?php 
	$trigger = TriggerManager::get(prmGET('id', $_SESSION['copyTrigger']));
	$filter = FilterManager::getByName(prmGET('name'));
	$filter_params = sessPOST('filter_params');
?>
<div id="filter-parameters">
<?php if (empty($filter) || count($filter->parameters()) == 0) { ?>
	<p>Select filter function to be applied or leave it blank for none.</p>
<?php } else { ?>
	<p><?php echo htmlentities($filter->description()); ?></p>
	<fieldset class="group">
		<legend>Filter parameters</legend>
		<?php foreach ($filter->parameters() as $parameter) { ?>
			<?php 
				$value = empty($filter_params[$parameter->Name]) ? 
					(empty($trigger) ? 
						'' 
						: 
						$trigger->parameterValue('filter', $parameter->Name)
					)
					:
					$filter_params[$parameter->Name]
				;
			?>
			<label><?php echo $parameter->Name; ?></label> 
			<?php 
				$editor = (empty($parameter->Editor) ?
					'default'
					:
					$parameter->Editor
				);
				echo $this->__call('/triggers/parameters/'.$editor, array('filter', $parameter, $value));
			?>
		<?php } ?>
	</fieldset>
<?php } ?>	
</div>
