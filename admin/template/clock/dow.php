<?php 
	list(
		$selected
	) = array_pad($this->_parameters_, 1, NULL);
	
	$checked = 'checked="checked"';
?>
<div class="group">
	<label for="mon">Monday</label>    <input type="checkbox" id="mon" name="dow[1]" value= "2" <?php echo (($selected &  2) != 0 ? $checked : ''); ?>/> 
	<label for="tue">Tuesday</label>   <input type="checkbox" id="tue" name="dow[2]" value= "4" <?php echo (($selected &  4) != 0 ? $checked : ''); ?>/> 
	<label for="wed">Wednesday</label> <input type="checkbox" id="wed" name="dow[3]" value= "8" <?php echo (($selected &  8) != 0 ? $checked : ''); ?>/> 
	<label for="thu">Thursday</label>  <input type="checkbox" id="thu" name="dow[4]" value="16" <?php echo (($selected & 16) != 0 ? $checked : ''); ?>/> 
	<label for="fri">Friday</label>    <input type="checkbox" id="fri" name="dow[5]" value="32" <?php echo (($selected & 32) != 0 ? $checked : ''); ?>/> 
	<label for="sat">Saturday</label>  <input type="checkbox" id="sat" name="dow[6]" value="64" <?php echo (($selected & 64) != 0 ? $checked : ''); ?>/> 
	<label for="sun">Sunday</label>    <input type="checkbox" id="sun" name="dow[0]" value= "1" <?php echo (($selected &  1) != 0 ? $checked : ''); ?>/> 
</div>