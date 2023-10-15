<?php

class db {
	private $dbh;
	private $stmt;

	function __construct() {
		try {
			$this->dbh = new PDO(
				DB_TYPE.':host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4;',
				DB_USER, DB_PASS
			);
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(Exception $e) {
			error_log($e->getMessage());
			throw new Exception('Failed to establish database connection to ›'.DB_HOST.'‹. Gentle panic.');
		}
	}

	public function getDbHandle() {
		return $this->dbh;
	}
	public function getLastStatement() {
		return $this->stmt;
	}

	public function existsSchema() {
		$this->stmt = $this->dbh->prepare(
			'SHOW TABLES LIKE "Client"'
		);
		$this->stmt->execute();
		foreach($this->stmt->fetchAll() as $row) {
			return true;
		}
	}

	private static function currentUtcDateTime() {
		return gmdate('Y-m-d H:i:s');
	}

	// Client Operations
	public function getClient($id) {
		$this->stmt = $this->dbh->prepare(
			'SELECT * FROM Client WHERE id = :id'
		);
		$this->stmt->execute([':id' => $id]);
		foreach($this->stmt->fetchAll(PDO::FETCH_CLASS, 'Client') as $row) {
			return $row;
		}
	}
	public function getClients() {
		$this->stmt = $this->dbh->prepare(
			'SELECT * FROM Client'
		);
		$this->stmt->execute();
		return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Client');
	}
	public function getClientByEmail($email) {
		$this->stmt = $this->dbh->prepare(
			'SELECT * FROM Client WHERE email = :email'
		);
		$this->stmt->execute([':email' => $email]);
		return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Client');
	}
	public function insertClient($email, $password, $activation_token) {
		if(count($this->getClientByEmail($email)) > 0) return -1;
		$this->stmt = $this->dbh->prepare(
			'INSERT INTO Client (email, password, pending_activation_token)
			VALUES (:email, :password, :activation_token)'
		);
		$this->stmt->execute([
			':email' => $email,
			':password' => $password,
			':activation_token' => $activation_token,
		]);
		return $this->dbh->lastInsertId();
	}
	public function setClientPassword($id, $password) {
		$this->stmt = $this->dbh->prepare(
			'UPDATE Client SET password = :password WHERE id = :id'
		);
		return $this->stmt->execute([
			':password' => $password,
			':id' => $id,
		]);
	}
	public function setClientActivity($id) {
		$this->stmt = $this->dbh->prepare(
			'UPDATE Client SET last_login = CURRENT_TIMESTAMP WHERE id = :id'
		);
		return $this->stmt->execute([
			':id' => $id,
		]);
	}
	public function setClientToken($id, $token) {
		$this->stmt = $this->dbh->prepare(
			'UPDATE Client SET pending_activation_token = :token WHERE id = :id'
		);
		return $this->stmt->execute([
			':id' => $id,
			':token' => $token,
		]);
	}
	public function setClientResetToken($id, $token) {
		$this->stmt = $this->dbh->prepare(
			'UPDATE Client SET pending_reset_token = :token WHERE id = :id'
		);
		return $this->stmt->execute([
			':id' => $id,
			':token' => $token,
		]);
	}
	public function setClientDeletionToken($id, $token) {
		$this->stmt = $this->dbh->prepare(
			'UPDATE Client SET pending_deletion_token = :token WHERE id = :id'
		);
		return $this->stmt->execute([
			':id' => $id,
			':token' => $token,
		]);
	}
	public function deleteClient($id) {
		$this->stmt = $this->dbh->prepare(
			'DELETE FROM Client WHERE id = :id'
		);
		return $this->stmt->execute([':id' => $id]);
	}

	// Customer Operations
	public function getCustomerByClient($clientId, $customerId) {
		$this->stmt = $this->dbh->prepare(
			'SELECT id, title, first_name, last_name, phone_home, phone_mobile, phone_work, email, street, zipcode, city, country, birthday, customer_group, newsletter, notes, custom_fields, image, consent, files, last_modified, removed
			FROM Customer WHERE client_id = :client_id AND id = :customer_id'
		);
		$this->stmt->execute([':client_id' => $clientId, ':customer_id' => $customerId]);
		foreach($this->stmt->fetchAll(PDO::FETCH_CLASS, 'Customer') as $result) {
			return $result;
		}
	}
	public function getCustomersByClient($clientId, $diffSince=null, $files=false) {
		// $files directly in all customers response for supporting older app versions
		if(!$diffSince) $diffSince = '1970-01-01 00:00:00';
		if($files) {
			$this->stmt = $this->dbh->prepare(
				'SELECT id, title, first_name, last_name, phone_home, phone_mobile, phone_work, email, street, zipcode, city, country, birthday, customer_group, newsletter, notes, custom_fields, image, consent, files, last_modified, removed
				FROM Customer WHERE client_id = :client_id AND last_modified_on_server > :diff_since'
			);
		} else {
			$this->stmt = $this->dbh->prepare(
				'SELECT id, title, first_name, last_name, phone_home, phone_mobile, phone_work, email, street, zipcode, city, country, birthday, customer_group, newsletter, notes, custom_fields, image, consent, IF(files IS NULL, NULL, TRUE) AS "files", last_modified, removed
				FROM Customer WHERE client_id = :client_id AND last_modified_on_server > :diff_since'
			);
		}
		$this->stmt->execute([':client_id' => $clientId, ':diff_since' => $diffSince]);
		return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Customer');
	}
	public function getActiveCustomersByClient($clientId) {
		$this->stmt = $this->dbh->prepare(
			'SELECT id, title, first_name, last_name, phone_home, phone_mobile, phone_work, email, street, zipcode, city, country, birthday, customer_group, newsletter, notes, custom_fields, image, consent, files, last_modified, removed
			FROM Customer WHERE removed = 0 AND client_id = :client_id'
		);
		$this->stmt->execute([':client_id' => $clientId]);
		return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Customer');
	}
	public function getActiveCustomerByClient($clientId, $id) {
		$this->stmt = $this->dbh->prepare(
			'SELECT id, title, first_name, last_name, phone_home, phone_mobile, phone_work, email, street, zipcode, city, country, birthday, customer_group, newsletter, notes, custom_fields, image, consent, files, last_modified, removed
			FROM Customer WHERE removed = 0 AND client_id = :client_id AND id = :id'
		);
		$this->stmt->execute([':client_id' => $clientId, ':id' => $id]);
		foreach($this->stmt->fetchAll(PDO::FETCH_CLASS, 'Customer') as $customer) {
			return $customer;
		}
	}
	public function markDeletedCustomerByClient($clientId, $id) {
		$this->stmt = $this->dbh->prepare(
			'UPDATE Customer
			SET title = "", first_name = "", last_name = "", phone_home = "", phone_mobile = "", phone_work = "", email = "", street = "", zipcode = "", city = "", country = "", birthday = NULL, customer_group = "", newsletter = 0, notes = "", custom_fields = "", image = NULL, consent = NULL, files = NULL, last_modified = :current_utc_time, last_modified_on_server = :current_utc_time, removed = 1
			WHERE client_id = :client_id AND id = :id'
		);
		return $this->stmt->execute([':client_id' => $clientId, ':id' => $id, ':current_utc_time' => self::currentUtcDateTime()]);
	}
	public function insertUpdateCustomer($clientId, $id, $title, $firstName, $lastName, $phoneHome, $phoneMobile, $phoneWork, $email, $street, $zipcode, $city, $country, $birthday, $customerGroup, $newsletter, $notes, $customFields, $image, $consentImage, $files, $lastModified, $removed) {

		// check if record exists
		$this->stmt = $this->dbh->prepare(
			'SELECT id, last_modified FROM Customer WHERE client_id = :client_id AND id = :id'
		);
		$checkResult = $this->stmt->execute([':client_id' => $clientId, ':id' => $id]);
		if(!$checkResult) throw new Exception('Could not check if customer exists');

		if($this->stmt->rowCount() > 0) {

			// update if last_modified is newer than in stored record
			$this->stmt = $this->dbh->prepare(
				'UPDATE Customer SET title = :title, first_name = :first_name, last_name = :last_name, phone_home = :phone_home, phone_mobile = :phone_mobile, phone_work = :phone_work, email = :email, street = :street, zipcode = :zipcode, city = :city, country = :country, birthday = :birthday, customer_group = :customer_group, newsletter = :newsletter, notes = :notes, custom_fields = :custom_fields, image = :image, consent = :consent, files = :files, last_modified = :last_modified, last_modified_on_server = :last_modified_on_server, removed = :removed
				WHERE client_id = :client_id AND id = :id AND last_modified < :last_modified'
			);
			return $this->stmt->execute([
				':client_id' => $clientId,
				':id' => $id,
				':title' => $title,
				':first_name' => $firstName,
				':last_name' => $lastName,
				':phone_home' => $phoneHome,
				':phone_mobile' => $phoneMobile,
				':phone_work' => $phoneWork,
				':email' => $email,
				':street' => $street,
				':zipcode' => $zipcode,
				':city' => $city,
				':country' => $country,
				':birthday' => $birthday,
				':customer_group' => $customerGroup,
				':newsletter' => $newsletter,
				':notes' => $notes,
				':custom_fields' => $customFields,
				':image' => $image,
				':consent' => $consentImage,
				':files' => $files,
				':last_modified' => $lastModified,
				':last_modified_on_server' => self::currentUtcDateTime(),
				':removed' => $removed,
			]);

		} else {

			// create new record
			$this->stmt = $this->dbh->prepare(
				'INSERT INTO Customer (client_id, id, title, first_name, last_name, phone_home, phone_mobile, phone_work, email, street, zipcode, city, country, birthday, customer_group, newsletter, notes, custom_fields, image, consent, files, last_modified, last_modified_on_server, removed)
				VALUES (:client_id, :id, :title, :first_name, :last_name, :phone_home, :phone_mobile, :phone_work, :email, :street, :zipcode, :city, :country, :birthday, :customer_group, :newsletter, :notes, :custom_fields, :image, :consent, :files, :last_modified, :last_modified_on_server, :removed)'
			);
			return $this->stmt->execute([
				':client_id' => $clientId,
				':id' => $id,
				':title' => $title,
				':first_name' => $firstName,
				':last_name' => $lastName,
				':phone_home' => $phoneHome,
				':phone_mobile' => $phoneMobile,
				':phone_work' => $phoneWork,
				':email' => $email,
				':street' => $street,
				':zipcode' => $zipcode,
				':city' => $city,
				':country' => $country,
				':birthday' => $birthday,
				':customer_group' => $customerGroup,
				':newsletter' => $newsletter,
				':notes' => $notes,
				':custom_fields' => $customFields,
				':image' => $image,
				':consent' => $consentImage,
				':files' => $files,
				':last_modified' => $lastModified,
				':last_modified_on_server' => self::currentUtcDateTime(),
				':removed' => $removed,
			]);

		}
	}

	// Voucher Operations
	public function getVouchersByClient($clientId, $diffSince=null) {
		if(!$diffSince) $diffSince = '1970-01-01 00:00:00';
		$this->stmt = $this->dbh->prepare(
			'SELECT * FROM Voucher WHERE client_id = :client_id AND last_modified_on_server > :diff_since'
		);
		$this->stmt->execute([':client_id' => $clientId, ':diff_since' => $diffSince]);
		return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Voucher', [$this->getCurrencyByClient($clientId)]);
	}
	public function getActiveVouchersByClient($clientId) {
		$this->stmt = $this->dbh->prepare(
			'SELECT * FROM Voucher WHERE removed = 0 AND client_id = :client_id'
		);
		$this->stmt->execute([':client_id' => $clientId]);
		return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Voucher', [$this->getCurrencyByClient($clientId)]);
	}
	public function getActiveVoucherByClient($clientId, $id) {
		$this->stmt = $this->dbh->prepare(
			'SELECT * FROM Voucher WHERE removed = 0 AND client_id = :client_id AND id = :id'
		);
		$this->stmt->execute([':client_id' => $clientId, ':id' => $id]);
		foreach($this->stmt->fetchAll(PDO::FETCH_CLASS, 'Voucher', [$this->getCurrencyByClient($clientId)]) as $voucher) {
			return $voucher;
		}
	}
	public function markDeletedVoucherByClient($clientId, $id) {
		$this->stmt = $this->dbh->prepare(
			'UPDATE Voucher
			SET original_value = 0, current_value = 0, voucher_no = "", from_customer = "", from_customer_id = NULL, for_customer = "", for_customer_id = NULL, valid_until = NULL, redeemed = NULL, notes = "", last_modified = :current_utc_time, last_modified_on_server = :current_utc_time, removed = 1
			WHERE client_id = :client_id AND id = :id'
		);
		return $this->stmt->execute([':client_id' => $clientId, ':id' => $id, ':current_utc_time' => self::currentUtcDateTime()]);
	}
	public function insertUpdateVoucher($clientId, $id, $originalValue, $currentValue, $voucherNo, $fromCustomer, $fromCustomerId, $forCustomer, $forCustomerId, $issued, $validUntil, $redeemed, $notes, $lastModified, $removed) {

		// check if record exists
		$this->stmt = $this->dbh->prepare(
			'SELECT id, last_modified FROM Voucher WHERE client_id = :client_id AND id = :id'
		);
		$checkResult = $this->stmt->execute([':client_id' => $clientId, ':id' => $id]);
		if(!$checkResult) throw new Exception('Could not check if voucher exists');

		if($this->stmt->rowCount() > 0) {

			// update if last_modified is newer than in stored record
			$this->stmt = $this->dbh->prepare(
				'UPDATE Voucher SET original_value = :original_value, current_value = :current_value, voucher_no = :voucher_no, from_customer = :from_customer, from_customer_id = :from_customer_id, for_customer = :for_customer, for_customer_id = :for_customer_id, issued = :issued, valid_until = :valid_until, redeemed = :redeemed, notes = :notes, last_modified = :last_modified, last_modified_on_server = :last_modified_on_server, removed = :removed
				WHERE client_id = :client_id AND id = :id AND last_modified < :last_modified'
			);
			return $this->stmt->execute([
				':client_id' => $clientId,
				':id' => $id,
				':original_value' => $originalValue,
				':current_value' => $currentValue,
				':voucher_no' => $voucherNo,
				':from_customer' => $fromCustomer,
				':from_customer_id' => $fromCustomerId,
				':for_customer' => $forCustomer,
				':for_customer_id' => $forCustomerId,
				':issued' => $issued,
				':valid_until' => $validUntil,
				':redeemed' => $redeemed,
				':notes' => $notes,
				':last_modified' => $lastModified,
				':last_modified_on_server' => self::currentUtcDateTime(),
				':removed' => $removed,
			]);

		} else {

			// create new record
			$this->stmt = $this->dbh->prepare(
				'INSERT INTO Voucher (client_id, id, original_value, current_value, voucher_no, from_customer, from_customer_id, for_customer, for_customer_id, issued, valid_until, redeemed, notes, last_modified, last_modified_on_server, removed)
				VALUES (:client_id, :id, :original_value, :current_value, :voucher_no, :from_customer, :from_customer_id, :for_customer, :for_customer_id, :issued, :valid_until, :redeemed, :notes, :last_modified, :last_modified_on_server, :removed)'
			);
			return $this->stmt->execute([
				':client_id' => $clientId,
				':id' => $id,
				':original_value' => $originalValue,
				':current_value' => $currentValue,
				':voucher_no' => $voucherNo,
				':from_customer' => $fromCustomer,
				':from_customer_id' => $fromCustomerId,
				':for_customer' => $forCustomer,
				':for_customer_id' => $forCustomerId,
				':issued' => $issued,
				':valid_until' => $validUntil,
				':redeemed' => $redeemed,
				':notes' => $notes,
				':last_modified' => $lastModified,
				':last_modified_on_server' => self::currentUtcDateTime(),
				':removed' => $removed,
			]);

		}
	}

	// Appointment Operations
	public function getAppointmentsByClient($clientId, $diffSince=null) {
		if(!$diffSince) $diffSince = '1970-01-01 00:00:00';
		$this->stmt = $this->dbh->prepare(
			'SELECT * FROM Appointment WHERE client_id = :id AND last_modified_on_server > :diff_since'
		);
		$this->stmt->execute([':id' => $clientId, ':diff_since' => $diffSince]);
		return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Appointment');
	}
	public function getActiveAppointmentsByClient($clientId) {
		$this->stmt = $this->dbh->prepare(
			'SELECT * FROM Appointment WHERE removed = 0 AND client_id = :client_id'
		);
		$this->stmt->execute([':client_id' => $clientId]);
		return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Appointment');
	}
	public function insertUpdateAppointment($clientId, $id, $calendarId, $title, $notes, $timeStart, $timeEnd, $fullday, $customer, $customerId, $location, $lastModified, $removed) {

		// check if record exists
		$this->stmt = $this->dbh->prepare(
			'SELECT id, last_modified FROM Appointment WHERE client_id = :client_id AND id = :id'
		);
		$checkResult = $this->stmt->execute([':client_id' => $clientId, ':id' => $id]);
		if(!$checkResult) throw new Exception('Could not check if appointment exists');

		if($this->stmt->rowCount() > 0) {

			// update if last_modified is newer than in stored record
			$this->stmt = $this->dbh->prepare(
				'UPDATE Appointment SET calendar_id = :calendar_id, title = :title, notes = :notes, time_start = :time_start, time_end = :time_end, fullday = :fullday, customer = :customer, customer_id = :customer_id, location = :location, last_modified = :last_modified, last_modified_on_server = :last_modified_on_server, removed = :removed
				WHERE client_id = :client_id AND id = :id AND last_modified < :last_modified'
			);
			return $this->stmt->execute([
				':client_id' => $clientId,
				':id' => $id,
				':calendar_id' => $calendarId,
				':title' => $title,
				':notes' => $notes,
				':time_start' => $timeStart,
				':time_end' => $timeEnd,
				':fullday' => $fullday,
				':customer' => $customer,
				':customer_id' => $customerId,
				':location' => $location,
				':last_modified' => $lastModified,
				':last_modified_on_server' => self::currentUtcDateTime(),
				':removed' => $removed,
			]);

		} else {

			// create new record
			$this->stmt = $this->dbh->prepare(
				'INSERT INTO Appointment (client_id, id, calendar_id, title, notes, time_start, time_end, fullday, customer, customer_id, location, last_modified, last_modified_on_server, removed)
				VALUES (:client_id, :id, :calendar_id, :title, :notes, :time_start, :time_end, :fullday, :customer, :customer_id, :location, :last_modified, :last_modified_on_server, :removed)'
			);
			return $this->stmt->execute([
				':client_id' => $clientId,
				':id' => $id,
				':calendar_id' => $calendarId,
				':title' => $title,
				':notes' => $notes,
				':time_start' => $timeStart,
				':time_end' => $timeEnd,
				':fullday' => $fullday,
				':customer' => $customer,
				':customer_id' => $customerId,
				':location' => $location,
				':last_modified' => $lastModified,
				':last_modified_on_server' => self::currentUtcDateTime(),
				':removed' => $removed,
			]);

		}
	}

	// Calendar Operations
	public function getCalendarsByClient($clientId, $diffSince=null) {
		if(!$diffSince) $diffSince = '1970-01-01 00:00:00';
		$this->stmt = $this->dbh->prepare(
			'SELECT * FROM Calendar WHERE client_id = :id AND last_modified_on_server > :diff_since'
		);
		$this->stmt->execute([':id' => $clientId, ':diff_since' => $diffSince]);
		return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Calendar');
	}
	public function getActiveCalendarsByClient($clientId) {
		$this->stmt = $this->dbh->prepare(
			'SELECT * FROM Calendar WHERE removed = 0 AND client_id = :client_id'
		);
		$this->stmt->execute([':client_id' => $clientId]);
		return $this->stmt->fetchAll(PDO::FETCH_CLASS, 'Calendar');
	}
	public function insertUpdateCalendar($clientId, $id, $title, $color, $notes, $lastModified, $removed) {

		// check if record exists
		$this->stmt = $this->dbh->prepare(
			'SELECT id, last_modified FROM Calendar WHERE client_id = :client_id AND id = :id'
		);
		$checkResult = $this->stmt->execute([':client_id' => $clientId, ':id' => $id]);
		if(!$checkResult) throw new Exception('Could not check if appointment exists');

		if($this->stmt->rowCount() > 0) {

			// update if last_modified is newer than in stored record
			$this->stmt = $this->dbh->prepare(
				'UPDATE Calendar SET title = :title, color = :color, notes = :notes, last_modified = :last_modified, last_modified_on_server = :last_modified_on_server, removed = :removed
				WHERE client_id = :client_id AND id = :id AND last_modified < :last_modified'
			);
			return $this->stmt->execute([
				':client_id' => $clientId,
				':id' => $id,
				':title' => $title,
				':color' => $color,
				':notes' => $notes,
				':last_modified' => $lastModified,
				':last_modified_on_server' => self::currentUtcDateTime(),
				':removed' => $removed,
			]);

		} else {

			// create new record
			$this->stmt = $this->dbh->prepare(
				'INSERT INTO Calendar (client_id, id, title, color, notes, last_modified, last_modified_on_server, removed)
				VALUES (:client_id, :id, :title, :color, :notes, :last_modified, :last_modified_on_server, :removed)'
			);
			return $this->stmt->execute([
				':client_id' => $clientId,
				':id' => $id,
				':title' => $title,
				':color' => $color,
				':notes' => $notes,
				':last_modified' => $lastModified,
				':last_modified_on_server' => self::currentUtcDateTime(),
				':removed' => $removed,
			]);

		}
	}

	// Setting Operations
	public function getSettingsByClient($clientId) {
		$this->stmt = $this->dbh->prepare(
			'SELECT id, setting, value, last_modified FROM Setting WHERE client_id = :client_id'
		);
		$this->stmt->execute([':client_id' => $clientId]);
		return $this->stmt->fetchAll();
	}
	public function getSettingByClient($clientId, $setting) {
		$this->stmt = $this->dbh->prepare(
			'SELECT id, setting, value, last_modified FROM Setting WHERE client_id = :client_id AND setting = :setting'
		);
		$this->stmt->execute([':client_id' => $clientId, ':setting' => $setting]);
		foreach($this->stmt->fetchAll() as $s) {
			return $s['value'];
		}
		return null;
	}
	public function getCurrencyByClient($clientId) {
		return $this->getSettingByClient($clientId, 'currency') ?? DEFAULT_CURRENCY;
	}

}
