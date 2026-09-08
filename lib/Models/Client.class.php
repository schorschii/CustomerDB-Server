<?php

namespace Models;

class Client {
	public $id = -1;
	public $email;
	public $password;
	public $pending_activation_token;
	public $pending_reset_token;
	public $pending_deletion_token;
	public $last_login;
	public $check_payment;
}
