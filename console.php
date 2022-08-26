<?php
if(php_sapi_name() != 'cli')
	die('This script must be executed from command line.'."\n");

if(!isset($argv[1]))
	die('Please specify an action as first parameter (createuser|listuser|changeuserpasswd|deleteuser).'."\n");

require_once('lib/loader.php');

$null = null;
switch($argv[1]) {

	case 'createuser':
		try {
			if(!isset($argv[2]) || !isset($argv[3])) {
				throw new Exception('missing arguments, need <username> <password>');
			}
			if($db->insertClient($argv[2], password_hash($argv[3], PASSWORD_DEFAULT), $null) > 0) {
				echo $argv[1].': OK'."\n";
			} else {
				throw new Exception('database operation error');
			}
		} catch(Exception $e) {
			echo $argv[1].' ERROR: '.$e->getMessage()."\n";
			exit(1);
		}
		break;

	case 'listuser':
		try {
			$clients = $db->getClients();
			if($clients !== null) {
				foreach($clients as $client) {
					echo $client->id." ".$client->email." ".$client->last_login."\n";
				}
				echo $argv[1].': OK'."\n";
			} else {
				throw new Exception('database operation error');
			}
		} catch(Exception $e) {
			echo $argv[1].' ERROR: '.$e->getMessage()."\n";
			exit(1);
		}
		break;

	case 'changeuserpasswd':
		try {
			if(!isset($argv[2])) {
				throw new Exception('missing arguments, need <userid> <new-password>');
			}
			if($db->getClient(intval($argv[2])) !== null) {
				if($db->setClientPassword(intval($argv[2]), password_hash($argv[3], PASSWORD_DEFAULT))) {
					echo $argv[1].': OK'."\n";
				} else {
					throw new Exception('database operation error');
				}
			} else {
				throw new Exception('userid not found');
			}
		} catch(Exception $e) {
			echo $argv[1].' ERROR: '.$e->getMessage()."\n";
			exit(1);
		}
		break;

	case 'deleteuser':
		try {
			if(!isset($argv[2])) {
				throw new Exception('missing arguments, need <userid>');
			}
			if($db->getClient(intval($argv[2])) !== null) {
				if($db->deleteClient(intval($argv[2]))) {
					echo $argv[1].': OK'."\n";
				} else {
					throw new Exception('database operation error');
				}
			} else {
				throw new Exception('userid not found');
			}
		} catch(Exception $e) {
			echo $argv[1].' ERROR: '.$e->getMessage()."\n";
			exit(1);
		}
		break;

	default:
		echo $argv[1].' ERROR: unknown command'."\n";
		exit(1);

}
