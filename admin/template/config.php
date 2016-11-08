<?php 
$user = UserManager::current();

if (isset($_SESSION['post'])) {
	if ($user->isAdmin()) {
		foreach(sessPOST('system') as $name => $value) {
			Config::system($name, $value);
		}
	}

	foreach(sessPOST('user') as $name => $value) {
		Config::user($name, $value);
	}
}

$_SESSION['post'] = NULL;
unset($_SESSION['post']);

$options = Config::options();

$system_service_network = Config::system('service.network');
if (!empty($system_service_network)) {
	$options['system']['service']['network'][$system_service_network] = ' selected="selected"';
}

$system_nodes_unknown = Config::system('nodes.unknown');
if (!empty($system_nodes_unknown)) {
	$options['system']['nodes']['unknown'][$system_nodes_unknown] = ' selected="selected"';
}

$user_nodes_accept = Config::user('nodes.accept');
if (!empty($user_nodes_accept)) {
	$options['user']['nodes']['accept'][$user_nodes_accept] = ' selected="selected"';
}

$adminEditable = ($user->isAdmin() ? '' : ' disabled="disabled"');
?>
<?php echo $this->backArrow(); ?>
<h1>Configuration</h1>
<form method="POST" action="<?php echo $this->path(); ?>" class="container">
	<fieldset class="group">
		<legend>System</legend>
		
		<label>Service Name</label> <input type="text" name="system[service.name]" value="<?php echo Config::system('service.name'); ?>"<?php echo $adminEditable; ?>/>
		
		<label>e-mail from</label> <input type="text" name="system[email.from]" value="<?php echo Config::system('email.from'); ?>"<?php echo $adminEditable; ?>/>
		
		<label>Network configuration</label> 
		<select name="system[service.network]"<?php echo $adminEditable; ?>>
			<option value="admin-only"<?php echo $options['system']['service']['network']['admin-only']; ?>>Admin only</option>
			<option value="all-users"<?php  echo $options['system']['service']['network']['all-users'];  ?>>All users</option>
		</select>
		
		<label>Keep log [days]</label> <input type="number" name="system[log.days]"   min="0" max="90" value="<?php echo Config::system('log.days'); ?>"<?php echo $adminEditable; ?>/>
		<label>Token size</label>      <input type="number" name="system[token.size]" min="8" max="32" value="<?php echo Config::system('token.size'); ?>"<?php echo $adminEditable; ?>/>
		
		<label>Unknown nodes</label>
		<select name="system[nodes.unknown]"<?php echo $adminEditable; ?>>
			<option value="user"<?php   echo $options['system']['nodes']['unknown']['user'];   ?>>Personal config</option>
			<option value="reject"<?php echo $options['system']['nodes']['unknown']['reject']; ?>>Reject</option>
		</select>
	</fieldset>
	
	<fieldset class="group">
		<legend>Personal</legend>
		
		<label>Time zone</label> <input type="text" name="user[timezone]" value="<?php echo Config::user('timezone'); ?>"/>
		
		<label>New nodes</label>
		<select name="user[nodes.accept]">
			<option value="auto"<?php   echo $options['user']['nodes']['accept']['auto'];   ?>>Auto accept</option>
			<?php if (Config::system('nodes.unknown') == 'user') { ?>
			<option value="manual"<?php echo $options['user']['nodes']['accept']['manual']; ?>>Manually create</option>
			<?php } ?>
			<option value="reject"<?php echo $options['user']['nodes']['accept']['reject']; ?>>Reject</option>
		</select>
		
		<label>IFTTT Maker Key</label> <input type="text" name="user[ifttt.key]" value="<?php echo htmlentities(Config::user('ifttt.key')); ?>" />
	</fieldset>
	
	<button type="submit">Save</button>
	<?php if (empty($_SESSION['firstStart'])) { ?>
	<button type="button">Back</button>
	<?php } ?>
</form>
