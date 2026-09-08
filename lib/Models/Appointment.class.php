<?php

namespace Models;

class Appointment {
	public $id = -1;
	public $client_id;
	public $calendar_id;
	public $title;
	public $notes;
	public $time_start;
	public $time_end;
	public $fullday;
	public $customer;
	public $customer_id;
	public $location;
	public $last_modified;
	public $last_modified_on_server;
	public $removed;
}
