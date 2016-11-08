<?php 
$actions = ActionManager::getAll();
?>
<?php echo $this->backArrow(); ?>
<h1>Actions list</h1>
<div class="container">
<?php if (empty($actions)) { ?>
	<div class="status error">No actions found</div>
<?php } else { ?>
	<div class="box">
		<table>
			<tr>
				<th>Action</th>
			</tr>
		<?php foreach ($actions as $action) { ?>
			<tr>
				<td>
					<div class="action parameters">
						<?php echo $action->name(); ?>
						<div class="description"><?php echo $action->description(); ?></div>
						<?php if (count($action->parameters()) > 0) { ?>
							<table class="description">
							<?php foreach ($action->parameters() as $parameter) { ?>
								<tr>
									<th width="33%"><?php echo $parameter->Name; ?></th>
									<td width="33%"><?php echo $parameter->Type; ?></td>
									<td width="33%"><?php echo ($parameter->Required ? 'required' : ''); ?></td>
								</tr>
							<?php } ?>
							</table>
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
