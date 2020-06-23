<?php

function handleApiRequestData($srcdata) {
	global $db;

	$resdata = ['jsonrpc' => '2.0'];

	// authenticate
	$userId = null;
	$user = null;
	if(isset($srcdata['params']['username']) && isset($srcdata['params']['password'])) {
		$users = $db->getClientByEmail($srcdata['params']['username']);
		if(isset($users[0])) {
			if($users[0]->pending_activation_token !== null) {
				$resdata['result'] = null;
				$resdata['error'] = LANG['account_locked'];
				return $resdata;
			}
			if(password_verify($srcdata['params']['password'], $users[0]->password)) {
				$userId = $users[0]->id;
				$user = $users[0];
				$db->setClientActivity($userId);
			}
		}
	}
	if($userId === null || $user === null) {
		$resdata['result'] = null;
		$resdata['error'] = LANG['authentication_failed'];
		error_log('user '.$srcdata['params']['username'].': authentication failure');
		return $resdata;
	}

	// execute operation
	$resdata['id'] = $srcdata['id'];
	switch($srcdata['method']) {

		case 'customerdb.read':
			$customers = $db->getCustomersByClient($userId);
			$vouchers = $db->getVouchersByClient($userId);
			$calendars = $db->getCalendarsByClient($userId);
			$appointments = $db->getAppointmentsByClient($userId);
			foreach($customers as $customer) {
				if($customer->image != null)
					$customer->image = base64_encode($customer->image);
				if($customer->consent != null)
					$customer->consent = base64_encode($customer->consent);
			}
			$resdata['result'] = [
				'customers' => $customers,
				'vouchers' => $vouchers,
				'calendars' => $calendars,
				'appointments' => $appointments,
			];
			break;

		case 'customerdb.put':
			$success = true;
			$db->beginTransaction();

			// todo check if all attr delivered before accessing it in array
			foreach($srcdata['params']['customers'] as $customer) {
				foreach(['id', 'title', 'first_name', 'last_name', 'phone_home', 'phone_work',
				'email', 'street', 'zipcode', 'city', 'country', 'customer_group',
				'newsletter', 'notes', 'custom_fields', 'last_modified', 'removed'] as $attribute) {
					if(!isset($customer[$attribute])) {
						$success = false;
						break;
					}
				}
				if(!$success) break;
				$success = $success && $db->insertUpdateCustomer(
					$userId,
					$customer['id'],
					$customer['title'],
					$customer['first_name'],
					$customer['last_name'],
					$customer['phone_home'],
					$customer['phone_mobile'],
					$customer['phone_work'],
					$customer['email'],
					$customer['street'],
					$customer['zipcode'],
					$customer['city'],
					$customer['country'],
					isset($customer['birthday']) ? $customer['birthday'] : null,
					$customer['customer_group'],
					$customer['newsletter'],
					$customer['notes'],
					$customer['custom_fields'],
					isset($customer['image']) ? base64_decode($customer['image']) : null,
					isset($customer['consent']) ? base64_decode($customer['consent']) : null,
					$customer['last_modified'],
					$customer['removed']
				);
			}
			foreach($srcdata['params']['vouchers'] as $voucher) {
				foreach(['id', 'original_value', 'current_value', 'voucher_no', 'from_customer', 'for_customer',
				'issued', 'notes', 'last_modified', 'removed'] as $attribute) {
					if(!isset($voucher[$attribute])) {
						$success = false;
						break;
					}
				}
				if(!$success) break;
				$success = $success && $db->insertUpdateVoucher(
					$userId,
					$voucher['id'],
					$voucher['original_value'],
					$voucher['current_value'],
					$voucher['voucher_no'],
					$voucher['from_customer'],
					$voucher['for_customer'],
					$voucher['issued'],
					isset($voucher['valid_until']) ? $voucher['valid_until'] : null,
					isset($voucher['redeemed']) ? $voucher['redeemed'] : null,
					$voucher['notes'],
					$voucher['last_modified'],
					$voucher['removed']
				);
			}
			if(isset($srcdata['params']['calendars'])) {
				foreach($srcdata['params']['calendars'] as $calendar) {
					foreach(['id', 'title', 'color', 'notes', 'last_modified', 'removed'] as $attribute) {
						if(!isset($calendar[$attribute])) {
							$success = false;
							break;
						}
					}
					if(!$success) break;
					$success = $success && $db->insertUpdateCalendar(
						$userId,
						$calendar['id'],
						$calendar['title'],
						$calendar['color'],
						$calendar['notes'],
						$calendar['last_modified'],
						$calendar['removed']
					);
				}
			}
			if(isset($srcdata['params']['appointments'])) {
				foreach($srcdata['params']['appointments'] as $appointment) {
					foreach(['id', 'calendar_id', 'title', 'notes', 'fullday', 'customer', 'location', 'last_modified', 'removed'] as $attribute) {
						if(!isset($appointment[$attribute])) {
							$success = false;
							break;
						}
					}
					if(!$success) break;
					$success = $success && $db->insertUpdateAppointment(
						$userId,
						$appointment['id'],
						$appointment['calendar_id'],
						$appointment['title'],
						$appointment['notes'],
						isset($appointment['time_start']) ? $appointment['time_start'] : null,
						isset($appointment['time_end']) ? $appointment['time_end'] : null,
						$appointment['fullday'],
						$appointment['customer'],
						$appointment['location'],
						$appointment['last_modified'],
						$appointment['removed']
					);
				}
			}

			$db->commitTransaction();
			$resdata['result'] = $success;
			break;

		default:
			$resdata['result'] = null;
			$resdata['error'] = LANG['unknown_method'];

	}

	return $resdata;
}
