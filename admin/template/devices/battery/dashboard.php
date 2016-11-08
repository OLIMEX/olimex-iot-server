<fieldset name="Battery">
	<?php echo $this->__call('/properties/render', array($node, $device, 'BatteryState')); ?>
	<?php echo $this->__call('/properties/render', array($node, $device, 'BatteryPercent')); ?>
</fieldset>
