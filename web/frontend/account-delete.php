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
				if($client->pending_deletion_token === $_GET['token']) {
					if(!empty($_POST['delete'])) {

						$result = $db->deleteClient($client->id);
						if($result === true) {
							$info = LANG['account_deleted_successfully'];
						} else {
							$info = LANG['error_deleting_account'];
						}

					} else {
						$info = LANG['confirm_deletion'];
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
						<th><input type="hidden" name="delete" value="1"></th>
						<td><input type="submit" value="<?php echo LANG['delete']; ?>"></td>
					</tr>
				</table>
			</form>
		<?php } ?>
	</div>
	<?php require('foot.inc.php'); ?>
</body>
</html>
