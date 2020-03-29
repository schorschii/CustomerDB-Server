<?php

function handleApiRequestAccount($srcdata) {
	global $db;

	$resdata = ['jsonrpc' => '2.0'];

	// execute operation
	$resdata['id'] = $srcdata['id'];
	switch($srcdata['method']) {

		case 'account.register':
			if(isset($srcdata['params']['email']) && isset($srcdata['params']['password'])) {
				$register = new Account($db);
				$result = $register->register($srcdata['params']['email'], $srcdata['params']['password']);
				if($result > 0) {
					$resdata['result'] = true;
				} elseif($result == Account::ERR_ALREADY_EXISTS) {
					$resdata['result'] = false;
					$resdata['error'] = LANG['email_already_exists'];
				} elseif($result == Account::ERR_INVALID_EMAIL) {
					$resdata['result'] = false;
					$resdata['error'] = LANG['invalid_email_address'];
				} elseif($result == Account::ERR_REG_DISABLED) {
					$resdata['result'] = false;
					$resdata['error'] = LANG['registration_disabled'];
				} elseif($result == Account::ERR_INVALID_PASSWORD) {
					$resdata['result'] = false;
					$resdata['error'] = LANG['password_constraint_failed'];
				} else {
					$resdata['result'] = false;
					$resdata['error'] = LANG['unknown_error_contact_support'];
				}
			} else {
				$resdata['result'] = false;
				$resdata['error'] = LANG['invalid_request'];
			}
			break;

			case 'account.resetpwd':
				$account = new Account($db);
				$result = $account->requestPasswordReset($srcdata['params']['email']);
				if($result === 1) {
					$resdata['result'] = true;
				} elseif($result == Account::ERR_NOT_FOUND) {
					$resdata['result'] = false;
					$resdata['error'] = LANG['account_not_found_check_email'];
				} else {
					$resdata['result'] = false;
					$resdata['error'] = LANG['unknown_error_contact_support'];
				}
				break;

			case 'account.delete':
				$account = new Account($db);
				$result = $account->requestAccountDeletion($srcdata['params']['email']);
				if($result === 1) {
					$resdata['result'] = true;
				} elseif($result == Account::ERR_NOT_FOUND) {
					$resdata['result'] = false;
					$resdata['error'] = LANG['account_not_found_check_email'];
				} else {
					$resdata['result'] = false;
					$resdata['error'] = LANG['unknown_error_contact_support'];
				}
				break;

		default:
			$resdata['result'] = null;
			$resdata['error'] = LANG['unknown_method'];

	}

	// this api functions should be limited by fail2ban
	error_log('account request');

	return $resdata;
}
