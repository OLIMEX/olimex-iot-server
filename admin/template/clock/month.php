<?php 
	list(
		$selected
	) = array_pad($this->_parameters_, 1, NULL);
	
	$monthName = array(
		"January",
		"February",
		"March",
		"April",
		"May",
		"June",
		"July",
		"August",
		"September",
		"October",
		"November",
		"December"
	);
?>
<select name="month" id="month">
	<?php for ($month=0; $month<12; $month++) { ?>
		<option value="<?php echo $monthName[$month]; ?>" <?php echo ($selected == $monthName[$month] ? ' selected="selected"' : ''); ?>><?php echo substr($monthName[$month], 0, 3); ?></option>
	<?php } ?>
</select>
