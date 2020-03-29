<?php
require_once('session.php');
require_once('../../lib/loader.php');
if(!FRONTEND_ENABLED) die();

$view = 'start';
if(isset($_GET['view'])) {
	switch($_GET['view']) {
		case 'customer':
		case 'voucher':
		case 'calendar':
			$view = $_GET['view'];
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<?php require('head.inc.php'); ?>
</head>
<body>
	<div id="topmenu">
		<a href="?view=customer"><?php echo LANG['customers']; ?></a>
		<a href="?view=voucher"><?php echo LANG['vouchers']; ?></a>
		<a href="login.php?logout"><?php echo LANG['log_out']; ?></a>
	</div>
	<div id="maincontent">
		<?php if(file_exists('php/'.$view.'.php')) require('php/'.$view.'.php'); ?>
	</div>
	<?php require('foot.inc.php'); ?>
</body>
</html>
