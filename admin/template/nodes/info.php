<?php 
$node = empty($this->_parameters_) ?
	NodeManager::get(prmGET('id'))
	:
	$node = $this->_parameters_[0]
;

if (empty($node) || !($node instanceof Node)) {
	throw new Exception('Invalid node', 400);
}

$off = $node->active() ? '' : ' off';
?>
<div class="node container <?php echo $off; ?>" id="node-<?php echo $node->id(); ?>">
	<h2 class="wifi"><?php echo $node->name(); ?></h2>
	<div class="details">
		<span class="error"></span>
		<span class="ip"><?php echo $node->ip(); ?></span>
		<span class="token"><?php echo $node->token(); ?></span>
	</div>
	<div class="about">
		<?php if ($node->active()) { ?>
			<?php $about = $node->about(); ?>
			<fieldset name="Firmware" class="default">
				<legend>Firmware</legend>
				<label>Version</label>      <input type="text" name="Version"        value="<?php echo $about->Firmware->Version; ?>" disabled="disabled" />
				<label>Image</label>        <input type="text" name="Image"          value="<?php echo $about->Firmware->Image;   ?>" disabled="disabled" />
				<label>Boot</label>         <input type="text" name="Boot"           value="<?php echo $about->Firmware->Boot;    ?>" disabled="disabled" />
			</fieldset>
			<label>SDK Version</label>      <input type="text" name="SDKVersion"     value="<?php echo $about->SDKVersion;     ?>" disabled="disabled" />
			<label>Reset Info</label>       <input type="text" name="ResetInfo"      value="<?php echo $about->ResetInfo;      ?>" disabled="disabled" />
			<label>PHY Mode</label>         <input type="text" name="PHYMode"        value="<?php echo $about->PHYMode;        ?>" disabled="disabled" />
			<label>Access Point MAC</label> <input type="text" name="AccessPointMAC" value="<?php echo $about->AccessPointMAC; ?>" disabled="disabled" />
			<label>Station MAC</label>      <input type="text" name="StationMAC"     value="<?php echo $about->StationMAC;     ?>" disabled="disabled" />
		<?php } ?>
		
		<div class="devices">
			<?php foreach ($node->devices() as $device) { ?>
				<div class="device <?php echo $device->name(); ?><?php echo $off; ?>">
					<h3><?php echo $device->name(); ?></h3>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
