<?php 
if (empty($this->_parameters_)) {
	$date = prmGET('date');
	$month = prmGET('month');
	$discard = NULL;
} else {
	list(
		$date,
		$month,
		$discard
	) = array_pad($this->_parameters_, 3, NULL);
}

if (empty($date)) {
	$date = date('Y-m-d');
}

if (empty($month)) {
	$month = $date;
}

$discard = (array)$discard;
if (!in_array('date', $discard)) {
	$discard[] = 'date';
}

$today = new DateTime();
$date  = DateTime::createFromFormat('Y-m-d', $date);
$month = DateTime::createFromFormat('Y-m-d', $month);
$month->modify('first day of this month')->modify('+1 day');

$current = modifyDate($month, 'first day of this month');
if ($current->format('l') != 'Monday') {
	$current->modify('previous monday');
}

$last = modifyDate($month, 'last day of this month');
if ($last->format('l') != 'Sunday') {
	$last->modify('next sunday');
}

$url = Breadcrumb::current();
parse_str($url['query'], $query);
foreach ($discard as $p) {
	unset($query[$p]);
}
$url = $url['path'].'?'.http_build_query($query);
?>
<div class="calendar">
	<div class="header">
		<form action="/calendar" method="GET">
			<input type="hidden" name="month" value="<?php echo $month->format('Y-m-d'); ?>" />
			<input type="hidden" name="date"  value="<?php echo $date->format('Y-m-d'); ?>" />
			<button class="prev month">&lt;</button>
			<button class="next month">&gt;</button>
			<h2><?php echo $month->format('M Y'); ?></h2>
		</form>
		<div>Mon</div>
		<div>Tue</div>
		<div>Wen</div>
		<div>Thu</div>
		<div>Fri</div>
		<div>Sat</div>
		<div>Sun</div>
	</div>
	<?php 
		while ($current <= $last) {
			$class = 'day';
			if ($current->format('Y-m-d') == $today->format('Y-m-d')) {
				$class .= ' today';
			}
			
			if ($current->format('Y-m-d') == $date->format('Y-m-d')) {
				$class .= ' current';
			}
			
			if ($current->format('Y-m') != $month->format('Y-m')) {
				$class .= ' gray';
			}
			
			$day = '<a href="'.$url.'&date='.$current->format('Y-m-d').'">'.
				$current->format('d').
			'</a>';
			
			echo '<div class="'.$class.'">'.$day.'</div>';
			$current->modify('+1 day');
		} 
	?>
</div>