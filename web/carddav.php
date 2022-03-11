<?php

/*
	Read-only CardDAV implementation for the Customer Database Server
	It is intentionally kept very simple.
*/

// namespace definition
const NS_DAV            = 'DAV:';
const NS_CARDDAV        = 'urn:ietf:params:xml:ns:carddav';
const NS_CALENDARSERVER = 'http://calendarserver.org/ns/';

const CARDDAV_API_ERROR_TEXT = 'This is a CardDAV API and can therefore only be used with a CardDAV client and not with normal web browsers.';

require_once('../lib/loader.php');
if(!API_ENABLED) die('API is disabled');

// no XML parser warnings in error logs
libxml_use_internal_errors(true);

// check content type if given ( Evolution does not set a Content-Type :( )
if(isset($_SERVER['CONTENT_TYPE'])
&& !in_array(trim(explode(';',$_SERVER['CONTENT_TYPE'])[0]), ['application/xml', 'text/xml'])) {
	header('HTTP/1.1 400 Content Type Mismatch');
	die(CARDDAV_API_ERROR_TEXT);
}

// authenticate
$userId = null;
$user = null;
if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
	$users = $db->getClientByEmail($_SERVER['PHP_AUTH_USER']);
	if(isset($users[0])) {
		if($users[0]->pending_activation_token !== null) {
			header('HTTP/1.1 401 Not Authorized');
			die(LANG['account_locked']);
		}
		if(password_verify($_SERVER['PHP_AUTH_PW'], $users[0]->password)) {
			$userId = $users[0]->id;
			$user = $users[0];
			$db->setClientActivity($userId);
		}
	}
}
if($userId === null || $user === null) {
	if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
		// only log "authentication failure" if credentials were given, to not erroneously lockout clients via fail2ban
		error_log('user '.($_SERVER['PHP_AUTH_USER']??'(empty)').': authentication failure');
	}
	// most CardDAV clients do their first request without credentials, waiting for the "WWW-Authenticate" header
	// then, they retry the request with (basic) authentication
	header('HTTP/1.1 401 Not Authorized');
	header('WWW-Authenticate: Basic');
	die(LANG['authentication_failed']);
}

// process request
switch($_SERVER['REQUEST_METHOD']) {
	// PROPFIND ist the first request sent by the client to get meta information about the address book and the items stored in it.
	// On the basis of the etag, the client decides which entry should be download via the REPORT or GET method.
	case 'PROPFIND':
	// The REPORT method delivers requested contacts including all data to the client.
	case 'REPORT':

		// parse XML request
		try {
			$xml = new SimpleXMLElement(file_get_contents('php://input'));
			if($_SERVER['REQUEST_METHOD'] === 'PROPFIND'
			&& ($xml->getName() !== 'propfind' || !isset($xml->children(NS_DAV)->prop))) {
				throw new Exception('Invalid PROPFIND request.');
			}
			if($_SERVER['REQUEST_METHOD'] === 'REPORT'
			&& (!in_array($xml->getName(), ['addressbook-multiget', 'sync-collection']))) {
				throw new Exception('Invalid CardDAV REPORT request.');
			}
		} catch(Exception $e) {
			header('HTTP/1.1 400 Invalid Request');
			die($e->getMessage());
		}

		// get requested props
		$reqprops = getReqProps($xml);

		// get requested files
		$reqfiles = getReqHrefs($xml);

		// compile files
		$files = [];
		if($_SERVER['REQUEST_METHOD'] === 'PROPFIND') {
			$files[$_SERVER['REQUEST_URI']] = false; // query root (superior address book)
		}
		if(in_array('getetag', $reqprops) || in_array('address-data', $reqprops)) {
			// load customers if requested
			foreach($db->getActiveCustomersByClient($userId) as $customer) {
				$files[rtrim($_SERVER['REQUEST_URI'],'/').'/'.$customer->id] = $customer;
			}
		}

		// generate XML response
		$domtree = new DOMDocument('1.0', 'UTF-8');
		$xmlRoot = $domtree->appendChild($domtree->createElement('d:multistatus'));
		$xmlRoot->setAttribute('xmlns:d', 'DAV:');
		$xmlRoot->setAttribute('xmlns:c', 'urn:ietf:params:xml:ns:carddav');
		$xmlRoot->setAttribute('xmlns:cs', 'http://calendarserver.org/ns/');

		foreach($files as $file => $object) {
			// only return specific items if requested
			if(!in_array($file, $reqfiles, false) && !empty($reqfiles)) continue;

			$response = $xmlRoot->appendChild($domtree->createElement('d:response'));
			$response->appendChild($domtree->createElement('d:href', $file));

			$propstat = $response->appendChild($domtree->createElement('d:propstat'));
			$prop = $propstat->appendChild($domtree->createElement('d:prop'));

			foreach($reqprops as $reqprop) {
				switch($reqprop) {
					case 'current-user-privilege-set':
						$privilegeset = $prop->appendChild($domtree->createElement('d:current-user-privilege-set'));
						$privilege = $privilegeset->appendChild($domtree->createElement('d:privilege'));
						$privilege->appendChild($domtree->createElement('d:read'));
						$privilege = $privilegeset->appendChild($domtree->createElement('d:privilege'));
						$privilege->appendChild($domtree->createElement('d:read-acl'));
						$privilege = $privilegeset->appendChild($domtree->createElement('d:privilege'));
						$privilege->appendChild($domtree->createElement('d:read-current-user-privilege-set'));
						break;

					case 'sync-token':
						$prop->appendChild($domtree->createElement('d:sync-token', md5(time())));
						break;

					case 'owner':
						$owner = $prop->appendChild($domtree->createElement('d:owner'));
						$owner->appendChild($domtree->createElement('d:href', $_SERVER['REQUEST_URI']));
						break;

					case 'max-resource-size':
						$maxresourcesize = $prop->appendChild($domtree->createElement('c:max-resource-size', 10000000));
						break;
					case 'max-image-size':
						$maxresourcesize = $prop->appendChild($domtree->createElement('c:max-image-size', 10000000));
						break;

					case 'supported-report-set':
						$supportedreportset = $prop->appendChild($domtree->createElement('d:supported-report-set'));
						$supportedreport = $supportedreportset->appendChild($domtree->createElement('d:supported-report'));
						$report = $supportedreport->appendChild($domtree->createElement('d:report'));
						$report->appendChild($domtree->createElement('d:expand-property'));
						$supportedreport = $supportedreportset->appendChild($domtree->createElement('d:supported-report'));
						$report = $supportedreport->appendChild($domtree->createElement('d:report'));
						$report->appendChild($domtree->createElement('d:pricipal-match'));
						$supportedreport = $supportedreportset->appendChild($domtree->createElement('d:supported-report'));
						$report = $supportedreport->appendChild($domtree->createElement('d:report'));
						$report->appendChild($domtree->createElement('d:principal-property-search'));
						$supportedreport = $supportedreportset->appendChild($domtree->createElement('d:supported-report'));
						$report = $supportedreport->appendChild($domtree->createElement('d:report'));
						$report->appendChild($domtree->createElement('d:principal-search-property-set'));
						$supportedreport = $supportedreportset->appendChild($domtree->createElement('d:supported-report'));
						$report = $supportedreport->appendChild($domtree->createElement('d:report'));
						$report->appendChild($domtree->createElement('d:sync-collection'));
						$supportedreport = $supportedreportset->appendChild($domtree->createElement('d:supported-report'));
						$report = $supportedreport->appendChild($domtree->createElement('d:report'));
						$report->appendChild($domtree->createElement('c:addressbook-multiget'));
						$supportedreport = $supportedreportset->appendChild($domtree->createElement('d:supported-report'));
						$report = $supportedreport->appendChild($domtree->createElement('d:report'));
						$report->appendChild($domtree->createElement('c:addressbook-query'));
						break;

					case 'principal-collection-set':
						$principalcollectionset = $prop->appendChild($domtree->createElement('d:principal-collection-set'));
						$principalcollectionset->appendChild($domtree->createElement('d:href', $_SERVER['REQUEST_URI']));
						break;

					case 'email-address-set':
						$emailaddressset = $prop->appendChild($domtree->createElement('cs:email-address-set'));
						$emailaddressset->appendChild($domtree->createElement('cs:email-address', $user->email));
						break;

					case 'addressbook-home-set':
						$addressbookhomeset = $prop->appendChild($domtree->createElement('c:addressbook-home-set'));
						$addressbookhomeset->appendChild($domtree->createElement('d:href', $_SERVER['REQUEST_URI']));
						break;

					case 'current-user-principal':
						#$prop->appendChild($domtree->createElement('d:unauthenticated'));
						$currentuserprincipal = $prop->appendChild($domtree->createElement('d:current-user-principal'));
						$currentuserprincipal->appendChild($domtree->createElement('d:href', $_SERVER['REQUEST_URI']));
						break;

					case 'resourcetype':
						$resourcetype = $prop->appendChild($domtree->createElement('d:resourcetype'));
						if($object == null) {
							$resourcetype->appendChild($domtree->createElement('d:collection'));
							$resourcetype->appendChild($domtree->createElement('c:addressbook'));
						}
						break;

					case 'displayname':
						if($object == null) {
							$prop->appendChild($domtree->createElement('d:displayname', LANG['app_name']));
						}
						break;

					case 'supported-address-data':
						$supportedaddressdata = $prop->appendChild($domtree->createElement('c:supported-address-data'));
						$addressdatatype = $supportedaddressdata->appendChild($domtree->createElement('c:address-data-type'));
						$addressdatatype->setAttribute('content-type', 'text/vcard');
						$addressdatatype->setAttribute('version', '3.0');
						break;

					case 'getcontenttype':
						if($object != null) {
							$prop->appendChild($domtree->createElement('d:getcontenttype', 'text/x-vcard'));
						}
						break;

					case 'getetag':
						if($object != null) {
							$prop->appendChild($domtree->createElement('d:getetag', md5($object->last_modified)));
						}
						break;

				case 'address-data':
						if($object != null) {
							$addressdata = $domtree->createElement('c:address-data');
							$addressdata->appendChild($domtree->createTextNode(customerToVcard($object)));
							$prop->appendChild($addressdata);
						}
						break;
				}
			}

			$propstat->appendChild($domtree->createElement('d:status', 'HTTP/1.1 200 OK'));

		}

		header('HTTP/1.1 207 Multi-Status');
		header('Content-Type: application/xml; charset=utf-8');
		header('DAV: 1, 3, addressbook');
		#$domtree->formatOutput = true; // debug only
		echo $domtree->saveXML();
		break;

	// macOS contacts app is doing a GET request to retrieve single contacts if "addressbook-multiget" as supported report is not set
	case 'GET':
		foreach($db->getActiveCustomersByClient($userId) as $customer) {
			$pathparts = explode('/', $_SERVER['REQUEST_URI']);
			if(intval(end($pathparts)) === intval($customer->id)) {
				header('HTTP/1.1 200 OK');
				die(customerToVcard($customer));
			}
		}
		header('HTTP/1.1 404 Not Found');
		die(CARDDAV_API_ERROR_TEXT);
		break;

	// anwser OPTIONS requests for macOS/iOS contacts app
	case 'OPTIONS':
		header('HTTP/1.1 200 OK');
		header('Allow: OPTIONS, GET, PROPFIND, REPORT');
		header('DAV: 1, 3, addressbook');
		die();

	default:
		header('HTTP/1.1 405 Method Not Allowed');
		die(CARDDAV_API_ERROR_TEXT);
}

function getReqProps($xml) {
	$props = [];
	foreach($xml->children(NS_DAV)->prop->children(NS_DAV) as $prop) {
		$props[] = $prop->getName();
	}
	foreach($xml->children(NS_DAV)->prop->children(NS_CARDDAV) as $prop) {
		$props[] = $prop->getName();
	}
	foreach($xml->children(NS_DAV)->prop->children(NS_CALENDARSERVER) as $prop) {
		$props[] = $prop->getName();
	}
	return $props;
}
function getReqHrefs($xml) {
	$hrefs = [];
	foreach($xml->children(NS_DAV) as $child) {
		if($child->getName() == 'href') {
			$hrefs[] = (String)$child;
		}
	}
	return $hrefs;
}
function customerToVcard($customer) {
	$vcard  = 'BEGIN:VCARD'."\n";
	$vcard .= 'VERSION:3.0'."\n";
	$vcard .= 'PRODID:-//SieberSystems//CustomerDatabaseServer//EN'."\n";
	$vcard .= 'UID:'.$customer->id."\n";
	$vcard .= 'FN:'.escapeVcfValue(trim($customer->title.' '.$customer->first_name.' '.$customer->last_name))."\n";
	$vcard .= 'N:'.escapeVcfValue($customer->last_name).';'.escapeVcfValue($customer->first_name).';;'.escapeVcfValue($customer->title).";\n";
	if(!empty($customer->phone_home))
		$vcard .= 'TEL;TYPE=HOME:'.escapeVcfValue($customer->phone_home)."\n";
	if(!empty($customer->phone_mobile))
		$vcard .= 'TEL;TYPE=CELL:'.escapeVcfValue($customer->phone_mobile)."\n";
	if(!empty($customer->phone_work))
		$vcard .= 'TEL;TYPE=WORK:'.escapeVcfValue($customer->phone_work)."\n";
	if(!empty($customer->phone_email))
		$vcard .= 'EMAIL;INTERNET:'.escapeVcfValue($customer->email)."\n";
	if(!empty($customer->street) || !empty($customer->city) || !empty($customer->zipcode) || !empty($customer->country))
		$vcard .= 'ADR;TYPE=HOME:;;'.escapeVcfValue($customer->street).';'.escapeVcfValue($customer->city).';;'.escapeVcfValue($customer->zipcode).';'.escapeVcfValue($customer->country)."\n";
	if(!empty($customer->customer_group))
		$vcard .= 'ORG:'.escapeVcfValue($customer->customer_group)."\n";
	if(!empty($customer->birthday))
		$vcard .= 'BDAY:'.date('Ymd', strtotime($customer->birthday))."\n";
	if(!empty($customer->notes))
		$vcard .= 'NOTE:'.escapeVcfValue($customer->notes)."\n";
	if(!empty($customer->image))
		$vcard .= 'PHOTO;ENCODING=BASE64;JPEG:'.base64_encode($customer->image)."\n";
	$vcard .= 'END:VCARD'."\n";
	return $vcard;
}
function escapeVcfValue($value) {
	return str_replace("\n", "\\n", $value);
}
