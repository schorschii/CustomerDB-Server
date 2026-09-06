<?php

namespace Google;

require_once(__DIR__.'/../google-api-php-client/vendor/autoload.php');

class PlayStore {

	private $mAuthConfig;

	function __construct($authConfig) {
		$this->mAuthConfig = $authConfig;
	}

	function checkPlayStore($token) {
		try {
			$client = new \Google_Client();
			$client->setAuthConfig($this->mAuthConfig);
			$client->setApplicationName('CustomerDB_Server');
			$client->setScopes(['https://www.googleapis.com/auth/androidpublisher']);
			//$client->setAccessToken(''); // we should cache the access token
			if($client->isAccessTokenExpired()) {
				$client->refreshTokenWithAssertion();
			}
			//var_dump( $client->getAccessToken() ); // we should cache the access token
			$service = new \Google_Service_AndroidPublisher($client);
			$purchase = $service->purchases_subscriptions->get(PLAYSTORE_APPID, PLAYSTORE_SKUID, $token);
			$expiryTimeUnix = $purchase->expiryTimeMillis / 1000;
			//var_dump($purchase);
			return (time() < $expiryTimeUnix);
		} catch(\Google_Service_Exception $e) {
			error_log($e->getMessage());
		}
		return false;
	}

}
