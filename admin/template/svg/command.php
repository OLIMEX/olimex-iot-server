<?php 
list(
	$data
) = array_pad($this->_parameters_, 1, NULL);

if (empty($data)) {
	throw new Exception('Missing command data parameter');
}
?>

<table>
	<tr>
		<th colspan="2">Data</th>
	</tr>
	<?php foreach ($data->Data as $name => $value) { ?>
	<tr>
		<td><?php echo $name ?></td>
		<td><?php echo htmlentities($value); ?></td>
	</tr>
	<?php } ?>
</table>
