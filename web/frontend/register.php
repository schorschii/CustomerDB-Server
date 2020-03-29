<?php
require_once('../../lib/loader.php');
if(!FRONTEND_ENABLED) die();

$info = null;
if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['password2'])) {
	if($_POST['password'] === $_POST['password2']) {
		$register = new Account($db);
		$result = $register->register($_POST['email'], $_POST['password']);
		if($result > 0) {
			$info = LANG['registration_succeeded'];
		} elseif($result === Account::ERR_ALREADY_EXISTS) {
			$info = LANG['email_already_exists'];
		} elseif($result === Account::ERR_INVALID_EMAIL) {
			$info = LANG['invalid_email_address'];
		} elseif($result === Account::ERR_REG_DISABLED) {
			$info = LANG['registration_disabled'];
		} elseif($result === Account::ERR_INVALID_PASSWORD) {
			$info = LANG['password_constraint_failed'];
		} else {
			$info = LANG['unknown_error'];
		}
	} else {
		$info = LANG['passwords_do_not_match'];
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
					<td><input type="email" name="email"></td>
				</tr>
				<tr>
					<th><?php echo LANG['password']; ?>:</th>
					<td><input type="password" name="password" minlength="8"></td>
				</tr>
				<tr>
					<th><?php echo LANG['confirm_password']; ?>:</th>
					<td><input type="password" name="password2" minlength="8"></td>
				</tr>
				<tr>
					<th></th>
					<td><input type="submit" value="<?php echo LANG['create_account']; ?>"></td>
				</tr>
				<tr>
					<th></th>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<th></th>
					<td><a href="login.php"><button type="button"><?php echo LANG['back_to_login']; ?></button></a></td>
				</tr>
			</table>
		</form>
	</div>
	<?php require('foot.inc.php'); ?>
</body>
</html>
