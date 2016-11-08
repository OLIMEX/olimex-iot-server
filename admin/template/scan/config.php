<?php 
if (!Network::canScan()) {
	if (Config::system('service.network') == 'admin-only') {
		throw new Exception('Access denied', 401);
	}
	
	$reason = WiFi::check() == 0 ?
		'No WiFi interfaces found.'
		:
		'You are connected to the only WiFi interface available.'
	;
	throw new Exception('WiFi scan can not be done. '.$reason);
}

if (NetworkScan::isStarted()) {
	Breadcrumb::go('/scan/status');
	return;
}

define('PREG_GONFIG', '/^ESP_([A-Za-z]+)_([A-F0-9]{6})$/');

$interfaces = Network::getInterfaces();
$wifiConfigured = array();
foreach ($interfaces as $interface) {
	if ($interface->isWiFi() && $interface->isConfigured()) {
		if ($interface->SSID() != NULL && $interface->PSK() != NULL) {
			$wifiConfigured[$interface->SSID()] = $interface->PSK();
		}
	}
}

$ipAddresses = array();
foreach ($interfaces as $interface) {
	if (
		$interface->isEthernet() && 
		!$interface->isUSB() && 
		$interface->ip() != NULL
	) {
		$ipAddresses[] = $interface->ip();
	}
}
sort($ipAddresses);

$espAccessPoints = array();
foreach (WiFi::getAccessPoints() as $AP) {
	if ($AP->isESP8266()) {
		$espAccessPoints[] = $AP;
	} else {
		if (!isset($wifiConfigured[$AP->SSID()])) {
			$wifiConfigured[$AP->SSID()] = $AP->PSK();
		}
	}
}

usort(
	$espAccessPoints,
	function ($a, $b) {
		$ac = preg_match(PREG_GONFIG, $a->SSID());
		$bc = preg_match(PREG_GONFIG, $b->SSID());
		
		if ($ac == $bc) {
			if ($a->SSID() > $b->SSID()) {
				return 1;
			}
			return -1;
		}
		
		if ($ac > $bc) {
			return -1;
		}
		return 1;
	}
);

$apPSK = sessPOST('psk', NULL, 'olimex-ap');

$stationSSID = sessPOST('custom_ssid', NULL, reset(array_keys($wifiConfigured)));
$stationPSK  = sessPOST('custom_psk');
$stationCustom = !in_array($stationSSID, array_keys($wifiConfigured));

$iotHost = sessPOST('custom_host', NULL, reset($ipAddresses));
$iotCustom = !in_array($iotHost, $ipAddresses);

$_SESSION['post'] = NULL;
unset($_SESSION['post']);

?>
<script>
	var AccessPoints = {
		<?php foreach ($wifiConfigured as $SSID => $PSK) { ?>
			'<?php echo addslashes($SSID); ?>': '<?php echo addslashes($PSK); ?>',
		<?php } ?>
	};
</script>
<script src="<?php echo $this->version('/scripts/scan-config.js'); ?>"></script>
<?php echo $this->backArrow(); ?>
<?php echo $this->refreshArrow(); ?>
<h1>Auto configuration setup</h1>
<?php if (empty($espAccessPoints)) { ?>
	<div class="status error">No ESP8266 Nodes found</div>
	<button type="button">Back</button>
<?php } else { ?>
	<form method="POST" action="<?php echo $this->path(); ?>" class="scan-config">
		<p>Common parameters to be set for all auto-configured ESP8266 nodes.</p>
		<div class="container">
			<fieldset class="group">
				<legend>Password confirmation</legend>
				<label>Password</label> <input name="password" type="password" />
			</fieldset>
			
			<fieldset class="group">
				<legend>Access Point config</legend>
				<label>New PSK</label> <input name="psk" type="text" value="<?php echo $apPSK; ?>" />
			</fieldset>
			
			<fieldset class="group">
				<legend>Station config</legend>
				<label>WiFi Network</label>
				<select name="ssid">
					<?php foreach ($wifiConfigured as $SSID => $PSK) { ?>
						<option<?php echo ($stationSSID == $SSID ? ' selected="selected"' : ''); ?>><?php echo htmlentities($SSID); ?></option>
					<?php } ?>
					<option value=""<?php echo ($stationCustom ? ' selected="selected"' : ''); ?>>Custom...</option>
				</select>
				<div class="ssid">
					<label>SSID</label> <input name="custom_ssid" type="text" value="<?php echo htmlentities($stationSSID); ?>" />
				</div>
				<label>PSK</label>  <input name="custom_psk"  type="text" value="<?php echo htmlentities($stationPSK); ?>" />
			</fieldset>
			
			<fieldset class="group">
				<legend>IoT config</legend>
				<label>Host</label>
				<select name="host">
					<?php foreach ($ipAddresses as $ip) { ?>
						<option<?php echo ($iotHost == $ip ? ' selected="selected"' : ''); ?>><?php echo $ip; ?></option>
					<?php } ?>
					<option value=""<?php echo ($iotCustom ? ' selected="selected"' : ''); ?>>Custom...</option>
				</select>
				<div class="host">
					<label>Custom</label> <input name="custom_host" type="text" value="<?php echo htmlentities($iotHost); ?>" />
				</div>
			</fieldset>
		</div>
		
		<p>Discovered ESP8266 nodes. Click on node name to enable auto-configuration.</p>
		<?php foreach ($espAccessPoints as $esp) { ?>
			<?php 
			$config = preg_match(PREG_GONFIG, $esp->SSID());
			?>
			<div class="container node <?php echo ($config ? 'config' : ''); ?>">
				<h2 class="wifi"><?php echo $esp->SSID(); ?></h2>
				<span class="token"><?php echo $esp->address(); ?></span>
				<div class="<?php echo ($config ? 'config' : 'hidden'); ?>">
					<input name="scan[<?php echo $esp->address(); ?>][SSID]" type="hidden" value="<?php echo htmlentities($esp->SSID()); ?>" />
					<label>Configure</label>   <input name="scan[<?php echo $esp->address(); ?>][config]" type="checkbox" value="1" <?php echo ($config ? 'checked="checked"' : ''); ?>/><br/>
					<label>Name / SSID</label> <input name="scan[<?php echo $esp->address(); ?>][name]"   type="text" value="<?php echo htmlentities($esp->SSID()); ?>" />
				</div>
			</div>
		<?php } ?>
		<button type="submit">Configure</button>
		<?php if (empty($_SESSION['firstStart'])) { ?>
		<button type="button">Back</button>
		<?php } ?>
	</form>
	<?php if (!empty($_SESSION['firstStart'])) { ?>
	<form method="GET" action="/">
		<button type="submit">Continue</button>
	</form>
	<?php } ?>
	<div class="select-error">
		<p><span class="error">Please select at least one ESP8266 node!</span></p>
		<button type="button">OK</button>
	</div>
	<div class="confirmation">
		<p>Automatic configuration can take about a minute per selected node.</p>
		<p>Do you want to proceed?</p>
		<button type="button">Yes</button>
		<button type="button">No</button>
	</div>
<?php } ?>
