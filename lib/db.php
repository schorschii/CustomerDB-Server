<?php

class db {
	private $mysqli;
	private $statement;

	function __construct() {
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // debug
		$link = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		if($link->connect_error) {
			die(':-O !!! failed to establish database connection: ' . $link->connect_error);
		}
		$link->set_charset("utf8mb4");
		$this->mysqli = $link;
	}

	public function getDbHandle() {
		return $this->mysqli;
	}
	public function getLastStatement() {
		return $this->statement;
	}

	public function beginTransaction() {
		return $this->mysqli->autocommit(false);
	}
	public function commitTransaction() {
		return $this->mysqli->commit();
	}
	public function rollbackTransaction() {
		return $this->mysqli->rollback();
	}

	public static function getResultObjectArray($result) {
		$resultArray = [];
		while($row = $result->fetch_object()) {
			$resultArray[] = $row;
		}
		return $resultArray;
	}

	public function existsSchema() {
		$sql = "SHOW TABLES LIKE 'Client'";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		return ($result->num_rows == 1);
	}

	// Client Operations
	public function getClient($id) {
		$sql = "SELECT * FROM Client WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		if(!$this->statement->execute()) return null;
		$result = $this->statement->get_result();
		if($result->num_rows == 0) return null;
		while($row = $result->fetch_object()) {
			return $row;
		}
	}
	public function getClients() {
		$sql = "SELECT * FROM Client";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function getClientByEmail($email) {
		$sql = "SELECT * FROM Client WHERE email = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('s', $email)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function insertClient($email, $password, $activation_token) {
		if(count($this->getClientByEmail($email)) > 0) return -1;
		$sql = "INSERT INTO Client (email, password, pending_activation_token) VALUES (?,?,?)";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('sss', $email, $password, $activation_token)) return null;
		if(!$this->statement->execute()) return null;
		return $this->statement->insert_id;
	}
	public function setClientPassword($id, $password) {
		$sql = "UPDATE Client SET password = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('si', $password, $id)) return null;
		return $this->statement->execute();
	}
	public function setClientActivity($id) {
		$sql = "UPDATE Client SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}
	public function setClientToken($id, $token) {
		$sql = "UPDATE Client SET pending_activation_token = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('si', $token, $id)) return null;
		return $this->statement->execute();
	}
	public function setClientResetToken($id, $token) {
		$sql = "UPDATE Client SET pending_reset_token = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('si', $token, $id)) return null;
		return $this->statement->execute();
	}
	public function setClientDeletionToken($id, $token) {
		$sql = "UPDATE Client SET pending_deletion_token = ? WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('si', $token, $id)) return null;
		return $this->statement->execute();
	}
	public function deleteClient($id) {
		$sql = "DELETE FROM Client WHERE id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $id)) return null;
		return $this->statement->execute();
	}

	// Customer Operations
	public function getCustomersByClient($clientId) {
		$sql = "SELECT id, title, first_name, last_name, phone_home, phone_mobile, phone_work, email, street, zipcode, city, country, birthday, customer_group, newsletter, notes, custom_fields, image, consent, last_modified, removed FROM Customer WHERE client_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $clientId)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function insertUpdateCustomer($clientId, $id, $title, $firstName, $lastName, $phoneHome, $phoneMobile, $phoneWork, $email, $street, $zipcode, $city, $country, $birthday, $customerGroup, $newsletter, $notes, $customFields, $image, $consentImage, $lastModified, $removed) {
		// check if record exists
		$null = null;
		$sql = "SELECT id, last_modified FROM Customer WHERE client_id = ? AND id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return false;
		$this->statement->bind_param('ii', $clientId, $id);
		if(!$this->statement->execute()) return false;
		$result = $this->statement->get_result();
		if($result->num_rows > 0) {

			// update if last_modified is newer than in stored record
			$sql = "UPDATE Customer SET title = ?, first_name = ?, last_name = ?, phone_home = ?, phone_mobile = ?, phone_work = ?, email = ?, street = ?, zipcode = ?, city = ?, country = ?, birthday = ?, customer_group = ?, newsletter = ?, notes = ?, custom_fields = ?, image = ?, consent = ?, last_modified = ?, removed = ? WHERE client_id = ? AND id = ? AND last_modified < ?";
			if(!$this->statement = $this->mysqli->prepare($sql)) return false;
			if(!$this->statement->bind_param('sssssssssssssissbbsiiis', $title, $firstName, $lastName, $phoneHome, $phoneMobile, $phoneWork, $email, $street, $zipcode, $city, $country, $birthday, $customerGroup, $newsletter, $notes, $customFields, $null, $null, $lastModified, $removed, $clientId, $id, $lastModified)) return false;
			if($image != null) {
				if(!$this->statement->send_long_data(16, $image)) return false;
			}
			if($consentImage != null) {
				if(!$this->statement->send_long_data(17, $consentImage)) return false;
			}
			return $this->statement->execute();

		} else {

			// create new record
			$sql = "INSERT INTO Customer (client_id, id, title, first_name, last_name, phone_home, phone_mobile, phone_work, email, street, zipcode, city, country, birthday, customer_group, newsletter, notes, custom_fields, image, consent, last_modified, removed) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			if(!$this->statement = $this->mysqli->prepare($sql)) return false;
			if(!$this->statement->bind_param('iisssssssssssssissbbsi', $clientId, $id, $title, $firstName, $lastName, $phoneHome, $phoneMobile, $phoneWork, $email, $street, $zipcode, $city, $country, $birthday, $customerGroup, $newsletter, $notes, $customFields, $null, $null, $lastModified, $removed)) return false;
			if($image != null) {
				if(!$this->statement->send_long_data(18, $image)) return false;
			}
			if($consentImage != null) {
				if(!$this->statement->send_long_data(19, $consentImage)) return false;
			}
			return $this->statement->execute();

		}
	}

	// Voucher Operations
	public function getVouchersByClient($clientId) {
		$sql = "SELECT id, current_value, original_value, voucher_no, from_customer, for_customer, issued, valid_until, redeemed, notes, last_modified, removed FROM Voucher WHERE client_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $clientId)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}
	public function insertUpdateVoucher($clientId, $id, $originalValue, $currentValue, $voucherNo, $fromCustomer, $forCustomer, $issued, $validUntil, $redeemed, $notes, $lastModified, $removed) {
		// check if record exists
		$sql = "SELECT id, last_modified FROM Voucher WHERE client_id = ? AND id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return false;
		$this->statement->bind_param('ii', $clientId, $id);
		if(!$this->statement->execute()) return false;
		$result = $this->statement->get_result();
		if($result->num_rows > 0) {

			// update if last_modified is newer than in stored record
			$sql = "UPDATE Voucher SET original_value = ?, current_value = ?, voucher_no = ?, from_customer = ?, for_customer = ?, issued = ?, valid_until = ?, redeemed = ?, notes = ?, last_modified = ?, removed = ? WHERE client_id = ? AND id = ? AND last_modified < ?";
			if(!$this->statement = $this->mysqli->prepare($sql)) return false;
			if(!$this->statement->bind_param('ddssssssssiiis', $originalValue, $currentValue, $voucherNo, $fromCustomer, $forCustomer, $issued, $validUntil, $redeemed, $notes, $lastModified, $removed, $clientId, $id, $lastModified)) return false;
			return $this->statement->execute();

		} else {

			// create new record
			$sql = "INSERT INTO Voucher (client_id, id, original_value, current_value, voucher_no, from_customer, for_customer, issued, valid_until, redeemed, notes, last_modified, removed) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
			if(!$this->statement = $this->mysqli->prepare($sql)) return false;
			if(!$this->statement->bind_param('iiddssssssssi', $clientId, $id, $originalValue, $currentValue, $voucherNo, $fromCustomer, $forCustomer, $issued, $validUntil, $redeemed, $notes, $lastModified, $removed)) return false;
			return $this->statement->execute();

		}
	}

	// Appointment Operations
	public function getAppointmentsByClient($clientId) {
		$sql = "SELECT id, title, full_day, start_time, end_time, notes, last_modified, removed FROM Appointment WHERE client_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $clientId)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}

	// Setting Operations
	public function getSettingsByClient($clientId) {
		$sql = "SELECT id, title, value, last_modified FROM Setting WHERE client_id = ?";
		if(!$this->statement = $this->mysqli->prepare($sql)) return null;
		if(!$this->statement->bind_param('i', $clientId)) return null;
		if(!$this->statement->execute()) return null;
		return self::getResultObjectArray($this->statement->get_result());
	}

}
