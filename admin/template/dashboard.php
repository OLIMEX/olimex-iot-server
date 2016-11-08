<?php 
	$user = UserManager::current();
	if (empty($user)) {
		echo $this->home();
		return;
	}
	
	$_SESSION['firstStart'] = NULL;
	unset($_SESSION['firstStart']);
?>
<script src="<?php echo $this->version('/scripts/dashboard.js'); ?>"></script>
<script>
(function ($) {
	var done = false;
	$(document).on(
		'readyIoT',
		function () {
			if (done) {
				return;
			}
			done = true;
			$.iotEventsListen('<?php echo 'ws'.(empty($_SERVER['HTTPS']) ? '' : 's').'://'.$_SERVER['HTTP_HOST'].'/clients'; ?>');
		}
	);
})(jQuery);
</script>
<div class="menu">&equiv;
	<ul>
		<?php if (UserManager::isAdmin()) { ?>
		<li><a href="/users/list">Users</a></li>
		<?php } ?>
		<li><a href="/users/edit">My profile</a></li>
		<li><a href="/config">Configuration</a></li>
		<?php if (UserManager::isAdmin() && Network::getServerInterface()->isUSB()) { ?>
		<li><a href="/ssl">SSL Certificate</a></li>
		<?php } ?>
		<hr/>
		<?php if (Network::canConfigure()) { ?>
		<li><a href="/network">Network</a></li>
		<?php } ?>
		<?php if (Network::canScan()) { ?>
		<li><a href="/scan/config">WiFi scan and auto-configure</a></li>
		<?php } ?>
		<li><a href="/setup"  target="_blank">New node setup</a></li>
		<li><a href="/direct" target="_blank">Direct node connection</a></li>
		<?php if (NodeManager::canAdd()) {?>
		<li><a href="/nodes/add">Add new node</a></li>
		<?php } ?>
		<hr/>
		<li><a href="/devices/list">Devices</a></li>
		<li><a href="/properties/list">Properties</a></li>
		<li><a href="/filters/list">Filters</a></li>
		<li><a href="/actions/list">Actions</a></li>
		<li><a href="/triggers/list">Triggers</a></li>
		<hr/>
		<li align="right"><a href="/logout">Logout</a></li>
	</ul>
</div>

<h1><?php echo Config::system('service.name'); ?></h1>

<div class="toolbar">
	<a href="/users/edit" title="My profile"><span class="profile"/></a>
	<a href="/config" title="Configuration"><span class="config"/></a>
	<?php if (Network::canConfigure()) { ?>
	<a href="/network" title="Network"><span class="network"/></a>
	<?php } ?>
	<?php if (Network::canScan()) { ?>
	<a href="/scan/config" title="WiFi scan and auto-configure"><span class="wifi-scan"/></a>
	<?php } ?>
	<a href="/devices/list" title="Devices"><span class="devices-list"/></a>
	<a href="/properties/list" title="Properties"><span class="properties"/></a>
	<a href="/filters/list" title="Filters"><span class="filters"/></a>
	<a href="/actions/list" title="Actions"><span class="actions"/></a>
	<a href="/triggers/list" title="Triggers"><span class="triggers"/></a>
</div>

<?php echo $this->nodes(); ?>

<form action="/config/about" class="container about eventIoT">
	<input type="text" name="NodeName" class="title" disabled="disabled" />
	<fieldset name="About">
		<fieldset name="Firmware">
			<legend>Firmware</legend>
			<label>Version</label>      <input type="text" name="Version"        disabled="disabled" />
			<label>Image</label>        <input type="text" name="Image"          disabled="disabled" />
			<label>Boot</label>         <input type="text" name="Boot"           disabled="disabled" />
		</fieldset>
		<label>SDK Version</label>      <input type="text" name="SDKVersion"     disabled="disabled" />
		<label>Reset Info</label>       <input type="text" name="ResetInfo"      disabled="disabled" />
		<label>PHY Mode</label>         <input type="text" name="PHYMode"        disabled="disabled" />
		<label>Access Point MAC</label> <input type="text" name="AccessPointMAC" disabled="disabled" />
		<label>Station MAC</label>      <input type="text" name="StationMAC"     disabled="disabled" />
	</fieldset>
</form>

<form action="/config/station" class="container about eventIoT">
	<input type="text" name="NodeName" class="title" disabled="disabled" />
	<fieldset name="Station">
		<label>SSID</label>             <input type="text" name="SSID"     disabled="disabled" />
		<label>Hostname</label>         <input type="text" name="Hostname" disabled="disabled" />
		<fieldset name="IP">
			<legend>IP</legend>
			<label>Address</label>      <div class="catch"><input type="text" name="Address"  disabled="disabled" /></div>
			<label>NetMask</label>      <input type="text" name="NetMask"  disabled="disabled" />
			<label>Gateway</label>      <input type="text" name="Gateway"  disabled="disabled" />
		</fieldset>
	</fieldset>
</form>
