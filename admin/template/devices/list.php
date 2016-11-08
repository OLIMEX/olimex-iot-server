<?php 
$user = UserManager::current();
$devices = DeviceManager::getAll();
?>
<?php echo $this->backArrow(); ?>
<?php if ($user->isAdmin()) { ?>
<div class="menu">
	<a href="/devices/add">+</a>
</div>
<?php } ?>
<h1>Devices list</h1>
<div class="container">
<?php if (empty($devices)) { ?>
	<div class="status error">No devices found</div>
<?php } else { ?>
	<div class="box">
		<table>
			<tr>
				<th>Device</th>
			</tr>
		<?php foreach ($devices as $device) { ?>
			<tr>
				<td>
					<div class="parameters<?php echo ($user->isAdmin() ? ' editable' : ''); ?>">
						<?php echo $device; ?>
						<div class="description"><?php echo htmlentities($device->description()); ?></div>
					</div>
					<?php if ($user->isAdmin()) { ?>
					<div class="menu">
						&equiv;
						<ul>
							<li><a href="/devices/edit?id=<?php echo $device->id(); ?>">Edit</a></li>
							<li><a href="/devices/delete?id=<?php echo $device->id(); ?>">Delete</a></li>
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
