<script src="<?php echo $this->version('/scripts/emtr.js'); ?>"></script>
<fieldset class="group">
	<legend>Counters</legend>
	<?php echo $this->__call('/properties/render', array($node, $device, 'CounterActive',   'counter')); ?>
	<?php echo $this->__call('/properties/render', array($node, $device, 'CounterApparent', 'counter')); ?>
	<button type="button" class="emtr-reset">Reset</button>
</fieldset>
<fieldset class="group">
	<legend>Momentary</legend>
	<?php echo $this->__call('/properties/render', array($node, $device, 'VoltageRMS')); ?>
	<?php echo $this->__call('/properties/render', array($node, $device, 'CurrentRMS')); ?>
	<?php echo $this->__call('/properties/render', array($node, $device, 'ActivePower')); ?>
	<?php echo $this->__call('/properties/render', array($node, $device, 'ReactivePower')); ?>
	<?php echo $this->__call('/properties/render', array($node, $device, 'ApparentPower')); ?>
	<?php echo $this->__call('/properties/render', array($node, $device, 'PowerFactor')); ?>
	<?php echo $this->__call('/properties/render', array($node, $device, 'LineFrequency')); ?>
</fieldset>

<div class="confirmation">
	<p>Do you really want to reset counters?</p>
	<button type="button">Yes</button>
	<button type="button">No</button>
</div>
