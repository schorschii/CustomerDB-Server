<?php
if(php_sapi_name() != 'cli')
	die('This script must be executed from command line.'."\n");

if(!isset($argv[1]))
	die('Please specify an action as first parameter (createuser|listuser|changeuserpasswd|deleteuser).'."\n");

require_once('lib/loader.php');

try {

	$null = null;
	switch($argv[1]) {

		case 'createuser':
			if(!isset($argv[2]) || !isset($argv[3])) {
				throw new Exception('missing arguments, need <username> <password>');
			}
			if($db->insertClient($argv[2], password_hash($argv[3], PASSWORD_DEFAULT), $null) > 0) {
				echo $argv[1].': OK'."\n";
			} else {
				throw new Exception('database operation error');
			}
			break;

		case 'listuser':
			$clients = $db->getClients();
			if($clients !== null) {
				foreach($clients as $client) {
					echo $client->id." ".$client->email." ".$client->last_login."\n";
				}
				echo $argv[1].': OK'."\n";
			} else {
				throw new Exception('database operation error');
			}
			break;

		case 'changeuserpasswd':
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
			break;

		case 'deleteuser':
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
			break;

		default:
			throw new Exception('unknown command');

	}

} catch(Exception $e) {
	echo $argv[1].' ERROR: '.$e->getMessage()."\n";
	exit(1);
}
