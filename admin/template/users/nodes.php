<?php 
$allNodes = NodeManager::getAll($user);
?>
<?php if (empty($allNodes)) { ?>
	<span class="status error">No registered nodes found</span>
<?php } else { ?>
	<h3>User nodes</h3>
	<p>Click on the node's name to see detailed information.</p>
	<div class="nodes">
			<?php 
				foreach ($allNodes as $node) {
					echo $this->__call('/nodes/info', array($node));
				}
			?>
	</div>
<?php } ?>
