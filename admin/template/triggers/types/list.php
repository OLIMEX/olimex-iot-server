<?php 
if (!UserManager::isAdmin()) {
	throw new Exception('Access denied', 401);
}

$triggerTypes = TriggerType::getAll();
?>
<?php echo $this->backArrow(); ?>
<div class="menu">
	<a href="/triggers/types/add">+</a>
</div>
<h1>Trigger types list</h1>
<div class="container">
<?php if (empty($triggerTypes)) { ?>
	<div class="status error">No trigger types found</div>
<?php } else { ?>
	<div class="box">
		<table>
			<tr>
				<th>Trigger type</th>
			</tr>
		<?php foreach ($triggerTypes as $triggerType) { ?>
			<tr>
				<td>
					<div><?php echo $triggerType; ?></div>
				</td>
			</tr>
		<?php } ?>
		</table>
	</div>
<?php } ?>
	<button type="button">Back</button>
</div>
