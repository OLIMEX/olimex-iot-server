<?php 
if (!UserManager::isAdmin()) {
	throw new Exception('Access denied', 401);
}
$users = UserManager::getAll();
?>
<?php echo $this->backArrow(); ?>
<div class="menu"><a href="/users/add">+</a></div>
<h1>Users</h1>
<div class="container">
<?php if (empty($users)) { ?>
	<div class="status error">No users found</div>
<?php } else { ?>
	<div class="box">
		<table>
			<tr>
				<th>Username</th>
				<th>e-mail</th>
			</tr>
		<?php foreach ($users as $user) { ?>
			<tr>
				<td><div><a href="/users/edit?id=<?php echo $user->id(); ?>"><?php echo htmlentities($user->name()); ?></a></div></td>
				<td><div><?php echo htmlentities($user->email()); ?></div></td>
			</tr>
		<?php } ?>
		</table>
	</div>
<?php } ?>
	<button type="button">Back</button>
</div>
