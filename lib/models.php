<?php

class Client {
	public $id = -1;
	public $email;
	public $password;
	public $pending_activation_token;
	public $pending_reset_token;
	public $pending_deletion_token;
	public $last_login;
	public $check_payment;

	function __construct() {
	}
}
class Customer {
	public $id = -1;
	public $title;
	public $first_name;
	public $last_name;
	public $phone_home;
	public $phone_mobile;
	public $phone_work;
	public $email;
	public $street;
	public $zipcode;
	public $city;
	public $country;
	public $birthday;
	public $customer_group;
	public $newsletter;
	public $notes;
	public $custom_fields;
	public $image;
	public $consent;
	public $files;
	public $last_modified;
	public $removed;

	function __construct() {
	}

	public function getFullName() {
		return trim($this->title.' '.$this->first_name.' '.$this->last_name);
	}
	public function getFirstLine() {
		return trim($this->title.' '.$this->first_name.' '.$this->last_name);
	}
	public function getSecondLine() {
		$checkAttributes = [
			$this->phone_home,
			$this->phone_mobile,
			$this->phone_work,
			$this->email,
			$this->street,
			$this->zipcode,
			$this->city,
			$this->customer_group,
		];
		foreach($checkAttributes as $attribute) {
			if(!empty($attribute)) return $attribute;
		}
		return '';
	}
}
class Voucher {
	public $id = -1;
	public $original_value;
	public $current_value;
	public $voucher_no;
	public $from_customer;
	public $from_customer_id;
	public $for_customer;
	public $for_customer_id;
	public $issued;
	public $valid_until;
	public $redeemed;
	public $notes;
	public $last_modified;
	public $removed;

	private $currency;

	function __construct($currency='') {
		$this->currency = $currency;
	}

	function getFirstLine() {
		if($this->current_value == $this->original_value) {
			return $this->getCurrentValueString();
		} else {
			return $this->getCurrentValueString().' ('.$this->getOriginalValueString().')';
		}
	}
	function getSecondLine() {
		$checkAttributes = [
			$this->voucher_no,
			$this->id
		];
		foreach($checkAttributes as $attribute) {
			if(!empty($attribute)) return $attribute;
		}
		return '';
	}

	function getCurrentValueString() {
		return priceFormatDisplay($this->current_value).' '.$this->currency;
	}
	function getOriginalValueString() {
		return priceFormatDisplay($this->original_value).' '.$this->currency;
	}
}
