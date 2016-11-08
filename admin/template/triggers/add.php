<?php 

$copy = TriggerManager::get(prmGET('copy'));
$trigger = empty($copy) ?
	new Trigger(-1, UserManager::current())
	:
	clone $copy
;

$title = 'Add new trigger';
$action = 'Add';
$disabled = FALSE;

include('form.php');
