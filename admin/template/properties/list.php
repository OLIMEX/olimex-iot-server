<?php 
$user = UserManager::current();
$deviceID = prmGET('deviceID');
$properties = PropertyManager::getByDevice(
	DeviceManager::get($deviceID),
	TRUE
);
?>
<?php echo $this->backArrow(); ?>
<?php if ($user->isAdmin()) { ?>
<div class="menu">
	<a href="/properties/add">+</a>
</div>
<?php } ?>
<h1>Properties list</h1>
<div class="container">
	<form action="<?php echo Breadcrumb::current()['path']; ?>" method="GET">
		<label>Filter by device</label>
		<select name="deviceID" onchange="this.form.submit();">
			<option value="">-- All devices --</option>
			<?php foreach (DeviceManager::getAll() as $device) { ?>
			<option value="<?php echo $device->id(); ?>" <?php echo ($device->id() == $deviceID ? 'selected="selected"' : ''); ?>>
				<?php echo $device->name(); ?>
			</option>	
			<?php } ?>
		</select>
	</form>
	
<?php if (empty($properties)) { ?>
	<div class="status error">No properties found</div>
<?php } else { ?>
	<div class="box">
		<table>
			<tr>
				<th>Property</th>
			</tr>
		<?php foreach ($properties as $property) { ?>
			<tr>
				<td>
					<div class="parameters<?php echo ($user->isAdmin() ? ' editable' : ''); ?>">
						<?php echo $property; ?>
						<div class="description"><?php echo $property->description(); ?></div>
					</div>
					<?php if ($user->isAdmin()) { ?>
					<div class="menu">
						&equiv;
						<ul>
							<li><a href="/properties/edit?id=<?php echo $property->id(); ?>">Edit</a></li>
							<li><a href="/properties/delete?id=<?php echo $property->id(); ?>">Delete</a></li>
						</ul>
					</div>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
		</table>
	</div>
<?php } ?>
	<button type="button">Back</button>
</div>
