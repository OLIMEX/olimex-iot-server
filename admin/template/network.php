<?php 
if (!Network::canConfigure()) {
	throw new Exception('Access denied', 401);
}

$interfaces = Network::getInterfaces();
?>
<script src="<?php echo $this->version('/scripts/network.js'); ?>"></script>
<?php echo $this->backArrow(); ?>
<h1>Network</h1>
<?php foreach ($interfaces as $interface) { ?>
	<?php 
		if (!$interface->isEthernet()) continue; 
		
		$disabled = $interface->isServer() || $interface->isUSB() || !UserManager::isAdmin() ?
			' disabled="disabled"'
			:
			''
		;
		
		$ip_disabled = !empty($disabled) || $interface->dhcp() ?
			' disabled="disabled"'
			:
			''
		;
		
		$interfaceCSS = $interface->isWiFi() ?
			'wifi'
			:
			($interface->isUSB() ?
				'usb'
				:
				'lan'
			)
		;
	?>
	
	<div class="network container <?php echo $interface->name().($interface->isDown() ? ' down' : ''); ?>">
		<?php if ($interface->isRunning()) { ?>
			<span>Running</span>
		<?php } ?>
		<?php if ($interface->isUp()) { ?>
			<span>Up</span>
		<?php } ?>
		<h2 class="<?php echo $interfaceCSS; ?>"><?php echo $interface->name(); ?></h2>
		<span class="address"><?php echo $interface->address(); ?></span>
		<form method="POST" action="<?php echo $this->path(); ?>">
			<input type="hidden" name="interface" value="<?php echo $interface->name(); ?>" />
			<?php if ($interface->isUSB()) { ?>
			<p>You can not modify USB system interface.</p>
			<?php } else if ($interface->isServer()) { ?>
			<p>You can not modify interface you are connected to.</p>
			<?php } ?>
			<label>Auto</label><input name="auto" type="checkbox" value="1" <?php echo ($interface->auto() ? 'checked="checked"' : ''); ?> <?php echo $disabled; ?>/><br/>
			<label>DHCP</label><input name="dhcp" type="checkbox" value="1" <?php echo ($interface->dhcp() ? 'checked="checked"' : ''); ?> <?php echo $disabled; ?>/>
			
			<?php if ($interface->isWiFi()) { ?>
			<fieldset class="group">
				<legend>WiFi</legend>
				<label>SSID</label> <input name="ssid" type="text" value="<?php echo htmlentities($interface->SSID()); ?>" <?php echo $disabled; ?>/>
				<label>PSK</label>  <input name="psk"  type="text" value="<?php echo htmlentities($interface->PSK());  ?>" <?php echo $disabled; ?>/>
			</fieldset>
			<?php } ?>
			
			<fieldset class="group">
				<legend>TCP / IP</legend>    
				<label>IP</label>      <input name="ip"   type="text" value="<?php echo $interface->ip(); ?>"   <?php echo $ip_disabled; ?>/>
				<label>Mask</label>    <input name="mask" type="text" value="<?php echo $interface->mask(); ?>" <?php echo $ip_disabled; ?>/>
			</fieldset>
			
			<?php if (!$interface->isServer()) { ?>
				<?php if (UserManager::isAdmin()) { ?>
					<button type="submit">Apply</button>
					<?php if ($interface->isUp()) { ?>
					<button type="button">Restart</button>
					<button type="button">Down</button>
					<?php } else { ?>
					<button type="button">Up</button>
					<?php } ?>
				<?php } else { ?>
					<?php if ($interface->isUp()) { ?>
					<button type="button">Restart</button>
					<?php } else { ?>
					<button type="button">Up</button>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</form>
	</div>
<?php } ?>
<?php if (empty($_SESSION['firstStart'])) { ?>
<button type="button">Back</button>
<?php } else { ?>
	<form method="POST" action="<?php echo $this->path(); ?>">
		<input type="hidden" name="action" value="Done" />
		<button type="submit">Continue</button>
	</form>
<?php } ?>