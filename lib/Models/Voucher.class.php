<?php

namespace Models;

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
