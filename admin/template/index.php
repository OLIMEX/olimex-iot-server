<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="mobile-web-app-capable" content="yes" />
	
	<link rel="icon" sizes="16x16" href="/favicon.ico" />
	<link rel="icon" sizes="48x48" href="/images/olimex.png" />
	
	<link rel="apple-touch-icon" sizes="48x48" href="/images/olimex.png" />
	<link rel="apple-touch-icon-precomposed" sizes="48x48" href="/Olimex-48.png" />
	
	<link href="<?php echo $this->version('/style/layout.css'); ?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo $this->version('/style/style.css'); ?>"  rel="stylesheet" type="text/css" />
	
	<script src="<?php echo $this->version('/scripts/jquery-1.11.3.js'); ?>"      type="text/javascript"></script>
	<script src="<?php echo $this->version('/scripts/advanced-json-path.js'); ?>" type="text/javascript"></script>
	<script src="<?php echo $this->version('/scripts/drag.js'); ?>"               type="text/javascript"></script>
	<script src="<?php echo $this->version('/scripts/swipe.js'); ?>"              type="text/javascript"></script>
	<script src="<?php echo $this->version('/scripts/lib.js'); ?>"                type="text/javascript"></script>
	
	<title><?php echo ($this->isRoot() ? '' : $this->title().' - '); ?><?php echo Config::system('service.name'); ?></title>
</head>

<body>
	<div class="content">
		<div class="status eventIoT">
			<?php if (!empty($_SESSION['error'])) { ?>
				<span class="error"><?php echo $_SESSION['error']; ?></span>
			<?php } else if (!empty($_SESSION['success'])) { ?>
				<span class="success">Success</span>
			<?php } else { ?>
				<span>&nbsp;</span>
			<?php } ?>
			<?php 
				$_SESSION['error'] = NULL;
				unset($_SESSION['error']);
				
				$_SESSION['success'] = NULL;
				unset($_SESSION['success']);
			?>
		</div>
		
		<?php if ($user = UserManager::current()) { ?>
			<p class="user">
				Logged in as <a href="/users/edit"><?php echo $user->name(); ?></a>
			</p>
		<?php } ?>
		
		<div></div>
		<?php 
			try {
				if ($this->isRoot()) {
					echo $this->dashboard(); 
				} else {
					echo $this->__call($this->path());
				}
			} catch (Exception $e) {
				?><h1>Error</h1><?php 
				HandleError($e);
			}
		?>
	</div>
</body>
</html>