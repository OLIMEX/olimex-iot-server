<?php 
list(
	$data
) = array_pad($this->_parameters_, 1, NULL);

if (empty($data)) {
	throw new Exception('Missing action data parameter');
}

if (!isset($data->Action) || !isset($data->Parameters)) {
	throw new Exception('Invalid action data parameter');
}

$params = $data->Parameters;
$action = ActionManager::getByName($data->Action);

if (empty($action)) {
	throw new Exception('Unknown action ['.$actionName.']');
}

?>

<table>
	<tr>
		<th colspan="2"><?php echo $action->name(); ?></th>
	</tr>
	<?php foreach ($action->parameters() as $p) { ?>
	<tr>
		<td><?php echo $p->Name ?></td>
		<td><?php echo htmlentities(property_exists($params, $p->Name) ? $params->{$p->Name} : ''); ?></td>
	</tr>
	<?php } ?>
</table>
