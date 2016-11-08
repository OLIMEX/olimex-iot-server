<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<style>
			p {
				font-family: Arial, Helvetica, sans-serif;
				font-weight: normal;
				font-size: 14px;
				line-height: 20px;
				color: black;
			}
		</style>
	</head>
	<body>
		<p>Hello <b><?php echo $user->name(); ?></b>.</p>
		<p>
			This is an automatically generated message. Please do not reply.
		</p>
		<p>
			<?php 
				$key = Cryptography::seal(
					json_encode(
						array(
							'key'  => $user->apiKey(),
							'code' => $passwordCode
						),
						JSON_FORCE_OBJECT
					)
				);
			?>
			You are receiving this e-mail in response to your request to change forgotten password.<br/>
			<a href="<?php echo server_url(); ?>/password?key=<?php echo $key; ?>">
				Click here if you want to reset your password.
			</a>
		</p>
		<p>
			Best Regards,<br/>
			Customer Support Team
		</p>
	</body>
</html>
