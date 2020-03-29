<?php
session_start();
require_once('../../lib/loader.php');
if(!FRONTEND_ENABLED) die();

$info = null;
if(isset($_POST['username']) && isset($_POST['password'])) {
	$result = $db->getClientByEmail($_POST['username']);
	if(count($result) == 1) {
		if(password_verify($_POST['password'], $result[0]->password)) {
			$db->setClientActivity($result[0]->id);
			$_SESSION['user_id'] = $result[0]->id;
			$_SESSION['user_email'] = $result[0]->email;
			header("Location: index.php");
			die();
		} else {
			$info = LANG['password_incorrect'];
		}
	} else {
		$info = LANG['username_not_exists'];
	}
}
elseif(isset($_GET['logout'])) {
	if(isset($_SESSION['user_id'])) {
		session_unset();
		session_destroy();
		$info = LANG['logout_successful'];
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<?php require('head.inc.php'); ?>
</head>
<body class="centerbox">
	<div id="centerbox">
		<div id="logocontainer">
			<img src="img/logo.svg" id="logo">
			<div id="textcontainer">
				<div id="vendor"><?php echo LANG['vendor']; ?></div>
				<div id="product"><?php echo LANG['app_name']; ?><sup id="web"><?php echo LANG['app_subtitle']; ?></sup></div>
			</div>
		</div>
		<?php if($info != null) { ?>
			<div class="infobox"><?php echo htmlspecialchars($info); ?></div>
		<?php } ?>
		<form method="POST">
			<table>
				<tr>
					<th><?php echo LANG['email_address']; ?>:</th>
					<td><input type="email" name="username"></td>
				</tr>
				<tr>
					<th><?php echo LANG['password']; ?>:</th>
					<td><input type="password" name="password"></td>
				</tr>
				<tr>
					<th></th>
					<td><input type="submit" value="<?php echo LANG['login']; ?>"></td>
				</tr>
				<tr>
					<th></th>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<th></th>
					<td><a href="register.php"><button type="button"><?php echo LANG['create_account']; ?></button></a></td>
				</tr>
			</table>
		</form>
	</div>
	<?php require('foot.inc.php'); ?>
</body>
</html>
