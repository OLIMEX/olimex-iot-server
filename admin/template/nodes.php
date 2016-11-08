<?php 

$allNodes = NodeManager::getAll();

?>
<div class="nodes eventIoT">
	<?php if (empty($allNodes)) { ?>
		<span class="status error">No registered nodes found</span>
		<p class="guide">
			<a href="/setup">Click here</a> to setup your first node.
		</p>
	<?php } else { ?>
		<?php 
			foreach ($allNodes as $n) {
				echo $this->__call('/nodes/dashboard', array($n));
			}
		?>
	<?php } ?>
	
	<p>
		<?php if (NodeManager::canAdd()) {?>
		<a href="/nodes/add">Add new node</a><br/>
		<?php } ?>
		<a href="/setup" target="_blank">New node setup</a>
	</p>
</div>
