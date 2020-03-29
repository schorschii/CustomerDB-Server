<?php
require_once('../../lib/loader.php');
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
		<?php
		$showform = false;
		$info = LANG['invalid_request'];
		if(!empty($_GET['userid']) && !empty($_GET['token'])) {
			$client = $db->getClient($_GET['userid']);
			if($client != null) {
				$null = null;
				if($client->pending_reset_token === $_GET['token']) {
					if(!empty($_POST['password']) && !empty($_POST['password2'])) {
						if($_POST['password'] === $_POST['password2']) {
							$a = new Account($db);
							$result = $a->changePassword($client->id, $_POST['password']);
							if($result === true) {
								$null = null;
								$db->setClientResetToken($client->id, $null);
								$info = LANG['password_changed_successfully'];
							} elseif($result === Account::ERR_INVALID_PASSWORD) {
								$info = LANG['password_constraint_failed'];
							} else {
								$info = LANG['error_changing_password'];
							}
						} else {
							$info = LANG['passwords_do_not_match'];
							$showform = true;
						}
					} else {
						$info = LANG['choose_new_password'];
						$showform = true;
					}
				} else {
					$info = LANG['invalid_token'];
				}
			}
		}
		?>
		<?php if($info != null) { ?>
			<div class="infobox"><?php echo htmlspecialchars($info); ?></div>
		<?php } ?>
		<?php if($showform) { ?>
			<form method="POST">
				<table>
					<tr>
						<th><?php echo LANG['new_password']; ?>:</th>
						<td><input type="password" name="password" minlength="8"></td>
					</tr>
					<tr>
						<th><?php echo LANG['confirm_password']; ?>:</th>
						<td><input type="password" name="password2" minlength="8"></td>
					</tr>
					<tr>
						<th></th>
						<td><input type="submit" value="<?php echo LANG['ok']; ?>"></td>
					</tr>
				</table>
			</form>
		<?php } ?>
	</div>
	<?php require('foot.inc.php'); ?>
</body>
</html>
