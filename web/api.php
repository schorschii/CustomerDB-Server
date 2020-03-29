<?php
require_once('../lib/loader.php');
if(!API_ENABLED) die('API is disabled');

// check content type
if(!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] != 'application/json') {
	header('HTTP/1.1 400 Content Type Mismatch'); die();
}

// get body
$body = file_get_contents('php://input');
$srcdata = json_decode($body, true);

// validate JSON-RPC
if($srcdata === null || !isset($srcdata['jsonrpc']) || $srcdata['jsonrpc'] != '2.0' || !isset($srcdata['method']) || !isset($srcdata['params']) || !isset($srcdata['id'])) {
	header('HTTP/1.1 400 Payload Corrupt'); die();
}

// execute operation
$resdata = null;
switch($srcdata['method']) {
	case 'customerdb.read':
	case 'customerdb.put':
		$resdata = handleApiRequestData($srcdata);
		break;

	case 'account.register':
	case 'account.resetpwd':
	case 'account.delete':
		$resdata = handleApiRequestAccount($srcdata);
		break;

	default:
		$resdata['result'] = null;
		$resdata['error'] = LANG['unknown_method'];
}

// return response
echo json_encode($resdata);
