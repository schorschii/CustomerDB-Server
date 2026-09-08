<?php

namespace Models;

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
