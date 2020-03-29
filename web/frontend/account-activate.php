<?php
require_once('../../lib/loader.php');

// unlock account (email link)
$info = LANG['invalid_request'];
if(!empty($_GET['userid']) && !empty($_GET['token'])) {
	$client = $db->getClient($_GET['userid']);
	if($client != null) {
		$null = null;
		if($client->pending_activation_token === $_GET['token']
			&& $db->setClientToken($client->id, $null)) {
			$info = LANG['account_successfully_activated'];
		} else {
			$info = LANG['invalid_token'];
		}
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
	</div>
	<?php require('foot.inc.php'); ?>
</body>
</html>
