<?php 
$id = prmGET('id');
if (empty($id)) {
	throw new Exception('Missing trigger ID', 400);
}

$trigger = TriggerManager::get($id);
if (empty($trigger)) {
	throw new Exception('Invalid trigger', 400);
}
?>

<?php echo $this->backArrow(); ?>
<h1>Trigger log</h1>
<div class="container">
	<div class="box">
		<table>
			<tr>
				<td class="th"><?php echo $trigger; ?></td>
			</tr>
			<?php foreach (TriggerManager::log($trigger) as $dbLog) { ?>
			<tr>
				<td>
					<div class="trigger parameters">
					<?php echo $dbLog['timestamp']; ?>
					<?php if (isset($dbLog['data']->Trigger->Parameters)) { ?>
						<table class="description">
						<?php foreach ($dbLog['data']->Trigger->Parameters as $name => $value) { ?>
							<tr>
								<th width="50%"><?php echo htmlentities($name); ?></th>
								<td width="50%"><?php echo htmlentities($value); ?></td>
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
	<button type="button">Back</button>
</div>
