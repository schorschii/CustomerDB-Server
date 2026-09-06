<?php

namespace Apple;

class StoreKit {

	const STOREKIT_URL_PROD = 'https://api.storekit.apple.com';
	const STOREKIT_URL_TEST = 'https://api.storekit-sandbox.apple.com';

	private $mIssuerId;
	private $mKeyId;
	private $mKey;

	function __construct($issuerId, $keyId, $key) {
		$this->mIssuerId = $issuerId;
		$this->mKeyId = $keyId;
		$this->mKey = $key;
	}

	function checkStoreKit($bundleId, $transactionId, $production=true) {
		try {
			$transaction = $this->getTransaction($bundleId, $transactionId, $production);
			if(self::isTransactionValid($transaction)) {
				return true;
			} else {
				throw new \RuntimeException('Apple StoreKit transaction is not valid '.$transactionId);
			}
		} catch(\RuntimeException $e) {
			error_log($e->getMessage());
		}
		return false;
	}

	private static function isTransactionValid($transaction) {
		return time() < intval($transaction['expiresDate']/1000);
	}

	function getTransaction($bundleId, $transactionId, $production=true) {
		// build JWT for authentication
		$jwt = Util\JsonWebToken::generateJwt(
			'ES256', $this->mKey, $this->mKeyId, $this->mIssuerId, 'appstoreconnect-v1', $bundleId
		);

		// build cURL request
		$baseUrl = $production ? self::STOREKIT_URL_PROD : self::STOREKIT_URL_TEST;
		$ch = curl_init($baseUrl.'/inApps/v1/transactions/'.rawurlencode($transactionId));
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				'Authorization: Bearer ' . $jwt,
				'Accept: application/json',
			],
		]);

		$response = curl_exec($ch);
		if($response === false) 
			throw new \RuntimeException('cURL error: ' . curl_error($ch));

		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($status !== 200)
			throw new \RuntimeException('Apple API returned code '.$status);

		$data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
		$signedTransactionInfo = $data['signedTransactionInfo']
			?? throw new \RuntimeException('signedTransactionInfo missing');

		// read JWS payload
		$parts = explode('.', $signedTransactionInfo);
		if(count($parts) !== 3)
			throw new \RuntimeException('Invalid JWS');

		$transaction = json_decode(
			Util\JsonWebToken::base64UrlDecode($parts[1]),
			true, 512, JSON_THROW_ON_ERROR
		);

		// read Transaction
		if(intval($transaction['transactionId'] ?? 0) !== intval($transactionId)) {
			throw new \RuntimeException('Transaction ID mismatch');
		}
		if(($transaction['bundleId'] ?? null) !== $bundleId) {
			throw new \RuntimeException('Bundle ID mismatch');
		}
		return $transaction;
	}

}
