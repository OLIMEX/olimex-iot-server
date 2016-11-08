<?php 
$filters = FilterManager::getAll();
?>
<?php echo $this->backArrow(); ?>
<h1>Filters list</h1>
<div class="container">
<?php if (empty($filters)) { ?>
	<div class="status error">No filters found</div>
<?php } else { ?>
	<div class="box">
		<table>
			<tr>
				<th>Filter</th>
			</tr>
		<?php foreach ($filters as $filter) { ?>
			<tr>
				<td>
					<div class="filter parameters">
						<?php echo $filter->name(); ?>
						<div class="description"><?php echo htmlentities($filter->description()); ?></div>
						<?php if (count($filter->parameters()) > 0) { ?>
							<table class="description">
							<?php foreach ($filter->parameters() as $parameter) { ?>
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
