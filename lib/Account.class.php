<?php

class Account {

	private $db;

	const ERR_ALREADY_EXISTS   = -1;
	const ERR_INVALID_EMAIL    = -2;
	const ERR_REG_DISABLED     = -3;
	const ERR_INVALID_PASSWORD = -4;
	const ERR_NOT_FOUND        = -5;
	const ERR_UNKNOWN          = -6;

	private $MAIL_HEADERS = [
		'MIME-Version: 1.0',
		'Content-type: text/plain; charset=utf-8',
		'From: ' . LANG['mail_sender'] . ' <' . MAIL_SENDER_ADDRESS . '>',
	];

	function __construct($db) {
		$this->db = $db;
	}

	function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$charactersLength = strlen($characters);
		$randomString = '';
		for($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	function checkPassword($password) {
		if(strlen($password) < 8) {
			return false;
		}
		// ... maybe more checks
		return true;
	}

	public function changePassword($userid, $password) {
		if(!$this->checkPassword($password)) {
			return self::ERR_INVALID_PASSWORD;
		}
		if($this->db->setClientPassword($userid, password_hash($password, PASSWORD_DEFAULT))) {
			return true;
		} else {
			return self::ERR_UNKNOWN;
		}
	}

	public function register($username, $password) {
		if(!$this->checkPassword($password)) {
			return self::ERR_INVALID_PASSWORD;
		}
		if(!REGISTRATION_ENABLED) {
			return self::ERR_REG_DISABLED;
		}
		if(!filter_var($username, FILTER_VALIDATE_EMAIL)) {
			return self::ERR_INVALID_EMAIL;
		}
		$token = VERIFY_EMAIL_REQUIRED ? $this->generateRandomString() : null;
		$result = $this->db->insertClient($username, password_hash($password, PASSWORD_DEFAULT), $token);
		if($result === -1) {
			return self::ERR_ALREADY_EXISTS;
		}
		elseif($result < 0) {
			return $result;
		}
		else {
			mail($username, LANG['registration_mail_subject'],
				LANG['registration_mail_text'] . "\n\n"
				.WEB_BASE_URL."/frontend/account-activate.php?userid=".$result."&token=".$token,
				implode("\r\n", $this->MAIL_HEADERS)
			);
		}
		return $result;
	}

	public function requestPasswordReset($email) {
		$result = $this->db->getClientByEmail($email);
		if(count($result) === 1) {
			$token = $this->generateRandomString();
			$this->db->setClientResetToken($result[0]->id, $token);
			mail($result[0]->email, LANG['password_reset_mail_subject'],
				LANG['password_reset_mail_text'] . "\n\n"
				.WEB_BASE_URL."/frontend/account-resetpwd.php?userid=".$result[0]->id."&token=".$token,
				implode("\r\n", $this->MAIL_HEADERS)
			);
			return 1;
		}
		elseif(count($result) === 0) {
			return self::ERR_NOT_FOUND;
		}
		return self::ERR_UNKNOWN;
	}

	public function requestAccountDeletion($email) {
		$result = $this->db->getClientByEmail($email);
		if(count($result) === 1) {
			$token = $this->generateRandomString();
			$this->db->setClientDeletionToken($result[0]->id, $token);
			mail($result[0]->email, LANG['deletion_mail_subject'],
				LANG['deletion_mail_text'] . "\n\n"
				.WEB_BASE_URL."/frontend/account-delete.php?userid=".$result[0]->id."&token=".$token,
				implode("\r\n", $this->MAIL_HEADERS)
			);
			return 1;
		}
		elseif(count($result) === 0) {
			return self::ERR_NOT_FOUND;
		}
		return self::ERR_UNKNOWN;
	}

}
