<?php
if(php_sapi_name() != "cli")
	die("This script must be executed from command line.\n");

if(!isset($argv[1]))
	die("Please specify an action as first parameter (createuser|listuser|changeuserpasswd|deleteuser).\n");

require_once('lib/loader.php');

$null = null;
switch($argv[1]) {

	case 'createuser':
		if(isset($argv[2]) && isset($argv[3])) {
			if($db->insertClient($argv[2], password_hash($argv[3], PASSWORD_DEFAULT), $null) > 0) {
				echo "createuser: ok\n";
			} else {
				echo "createuser: database operation error\n";
			}
		} else {
			echo "createuser: missing arguments, need <username> <password>\n";
		}
		break;

	case 'listuser':
		$clients = $db->getClients();
		if($clients !== null) {
			foreach($clients as $client) {
				echo $client->id." ".$client->email." ".$client->last_login."\n";
			}
			echo "listuser: ok\n";
		} else {
			echo "listuser: database operation error\n";
		}
		break;

	case 'changeuserpasswd':
		if(isset($argv[2])) {
			if($db->getClient(intval($argv[2])) !== null) {
				if($db->setClientPassword(intval($argv[2]), password_hash($argv[3], PASSWORD_DEFAULT))) {
					echo "changeuserpasswd: ok\n";
				} else {
					echo "changeuserpasswd: database operation error\n";
				}
			} else {
				echo "changeuserpasswd: userid not found\n";
			}
		} else {
			echo "changeuserpasswd: missing arguments, need <userid> <new-password>\n";
		}
		break;

	case 'deleteuser':
		if(isset($argv[2])) {
			if($db->getClient(intval($argv[2])) !== null) {
				if($db->deleteClient(intval($argv[2]))) {
					echo "deleteuser: ok\n";
				} else {
					echo "deleteuser: database operation error\n";
				}
			} else {
				echo "deleteuser: userid not found\n";
			}
		} else {
			echo "deleteuser: missing arguments, need <userid>\n";
		}
		break;

}
