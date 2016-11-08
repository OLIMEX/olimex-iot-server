<?php 
$_SESSION['post'] = NULL;
unset($_SESSION['post']);

$triggers = TriggerManager::getAll();
?>
<?php echo $this->backArrow(); ?>
<div class="menu">
	<?php if (UserManager::isAdmin()) { ?>
	&equiv;
	<ul>
		<li><a href="/triggers/add">Add new trigger</a></li>
		<hr/>
		<li><a href="/triggers/types/list">Trigger types</a></li>
	</ul>
	<?php } else { ?>
		<a href="/triggers/add">+</a>
	<?php } ?>
</div>
<h1>Triggers list</h1>
<div class="container">
<?php if (empty($triggers)) { ?>
	<div class="status error">No triggers found</div>
<?php } else { ?>
	<div class="box">
		<table>
			<tr>
				<th>Trigger</th>
			</tr>
		<?php foreach ($triggers as $trigger) { ?>
			<tr>
				<td>
					<div class="trigger parameters<?php echo ($trigger->editable() ? ' editable' : ''); ?><?php echo ($trigger->active() ? '' : ' disabled'); ?>">
						<?php echo $trigger; ?>
						<?php if (count($trigger->parameters('action')) > 0) { ?>
							<table class="description">
							<?php foreach ($trigger->parameters('action') as $parameter) { ?>
								<tr>
									<th width="50%"><?php echo htmlentities($parameter->name()); ?></th>
									<td width="50%"><?php echo htmlentities($parameter->value()); ?></td>
								</tr>
							<?php } ?>
							</table>
						<?php } ?>
						<?php if ($trigger->editable()) { ?>
							<div class="menu">
								&equiv;
								<ul>
									<?php if ($trigger->active()) { ?>
									<li><a href="/triggers/active?id=<?php echo $trigger->id(); ?>&set=0">Disable</a></li>
									<?php } else { ?>
									<li><a href="/triggers/active?id=<?php echo $trigger->id(); ?>&set=1">Enable</a></li>
									<?php } ?>
									<li><a href="/triggers/edit?id=<?php echo $trigger->id(); ?>">Edit</a></li>
									<li><a href="/triggers/add?copy=<?php echo $trigger->id(); ?>">Copy</a></li>
									<li><a href="/triggers/delete?id=<?php echo $trigger->id(); ?>">Delete</a></li>
									<hr/>
									<li><a href="/triggers/log?id=<?php echo $trigger->id(); ?>">View log</a></li>
								</ul>
							</div>
						<?php } ?>
					</div>
				</td>
			</tr>
		<?php } ?>
		</table>
	</div>
<?php } ?>
	<button type="button">Back</button>
</div>
