<?php 
echo $this->backArrow();
echo '<h1>ESP Access Point scan...</h1>';
?>
<div class="status pre">
<?php 
if (NetworkScan::isStarted()) { 
	header('Refresh: 3');
	echo NetworkScan::getStatus(); 
} else { 
	Breadcrumb::go('/');
	return;
} 
?>
</div>
<br/>
<button type="button">Back</button>
