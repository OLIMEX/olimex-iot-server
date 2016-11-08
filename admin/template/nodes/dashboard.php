<?php 
$node = empty($this->_parameters_) ?
	NodeManager::get(prmGET('id'))
	:
	$node = $this->_parameters_[0]
;

if (empty($node) || !($node instanceof Node)) {
	throw new Exception('Invalid node', 400);
}

?>
<div class="node container eventIoT<?php echo ($node->active() ? '' : ' off'); ?>" id="node-<?php echo $node->id(); ?>" data-connection="<?php echo $node->ip().':'.$node->port(); ?>">
	<div class="menu">
		&equiv;
		<ul>
			<li><a onclick="nodeReconnect('<?php echo $node->token(); ?>');">Reconnect</a></li>
			<li><a onclick="nodeRestart('<?php echo $node->token(); ?>');">Restart</a></li>
			<li><a href="/nodes/edit?id=<?php echo $node->id();?>">Edit node</a></li>
			<hr/>
			<li><a href="/nodes/delete?id=<?php echo $node->id();?>">Delete node</a></li>
			<hr/>
			<li><a onclick="nodeStation('<?php echo $node->token(); ?>');">Station info</a></li>
			<li><a onclick="nodeAbout('<?php echo $node->token(); ?>');">About</a></li>
		</ul>
	</div>
	<h2 class="wifi"><a target="_blank" href="/direct?host=<?php echo $node->ip();?>" title="Direct <?php echo $node->name(); ?> connection"><?php echo $node->name(); ?></a></h2>
	<div class="details">
		<span class="error"></span>
		<span class="ip"><?php echo $node->ip(); ?></span>
		<span class="token"><?php echo $node->token(); ?></span>
	</div>
	<div class="devices">
		<?php 
			foreach ($node->devices() as $device) { 
				try {
					echo $this->__call('/devices/dashboard', array($node, $device)); 
				} catch (Exception $e) {
					if (DEVELOPMENT) {
						echo '<div class="formIoT">'.$e->getMessage().'</div>';
					}
				}
			} 
		?>
	</div>
</div>
