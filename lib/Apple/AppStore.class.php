<?php

namespace Apple;

class AppStore {

	const APPSTORE_URL_PROD = 'https://buy.itunes.apple.com/verifyReceipt';
	const APPSTORE_URL_TEST = 'https://sandbox.itunes.apple.com/verifyReceipt';

	private $mSecret;

	function __construct($secret) {
		$this->mSecret = $secret;
	}

	function checkAppStore($receipt, $productId, $production=true) {
		$responseData = $this->getReceipt($receipt, $production);
		if(self::containsReceiptValidProduct($responseData, $productId)) {
			return true;
		} else {
			error_log('Apple AppStore receipt does not contain valid '.$productId);
		}
		return false;
	}

	private static function containsReceiptValidProduct($receiptData, $productId) {
		foreach($receiptData['receipt']['in_app'] as $purchase) {
			if($purchase['product_id'] === $productId
			&& time() < intval($purchase['expires_date_ms'])/1000) {
				return true;
			}
		}
		return false;
	}

	function getReceipt($receipt, $production=true) {
		$ch = curl_init($production ? self::APPSTORE_URL_PROD : self::APPSTORE_URL_TEST);
		curl_setopt_array($ch, array(
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
			),
			CURLOPT_POSTFIELDS => json_encode([
				'receipt-data' => $receipt,
				'password' => $this->mSecret,
			])
		));

		$response = curl_exec($ch);
		if($response === false)
			throw new \RuntimeException('Apple AppStore connection error: '.curl_error($ch));

		$responseData = json_decode($response, true);
		if(!isset($responseData['status']) || $responseData['status'] !== 0)
			throw new \RuntimeException('Apple AppStore receipt verification Error: '.$response);
		return $responseData;
	}

}
