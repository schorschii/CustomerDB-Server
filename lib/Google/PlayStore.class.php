<?php

namespace Google;

use \Apple\Util\JsonWebToken as JsonWebToken;

class PlayStore {

	const TOKEN_CACHE_PATH = '/tmp/google-token-cache.json';

	private $mAuthConfig;

	function __construct($authConfig) {
		$this->mAuthConfig = $authConfig;
	}

	private function aquireAccessToken() {
		// restore cached token
		$tokenJson = null;
		if(file_exists(self::TOKEN_CACHE_PATH))
			file_get_contents(self::TOKEN_CACHE_PATH);
		if($tokenJson) {
			$token = json_decode($tokenJson, true);
			if(empty($token['access_token'])
			|| empty($token['expires_in'])
			|| empty($token['created'])
			|| $token['created']+$token['expires_in'] < time()) {
				// renew token if invalid or expired
				$token = $this->getAccessToken();
			}
		} else {
			$token = $this->getAccessToken();
		}
		// cache the access token
		file_put_contents(self::TOKEN_CACHE_PATH, json_encode($token));
		// return the token value
		return $token['access_token'];
	}
	private function getAccessToken() {
		$creds = json_decode($this->mAuthConfig, true);
		$token = $this->apiCall('POST', 'https://www.googleapis.com/oauth2/v4/token', http_build_query([
			'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
			'assertion' => JsonWebToken::generateJwt('RS256',
				$creds['private_key'], $creds['private_key_id'], $creds['client_email'],
				'https://www.googleapis.com/oauth2/v4/token',
				null,
				'https://www.googleapis.com/auth/androidpublisher',
			)
		]), 200, []);
		if(empty($token['access_token'])
		|| empty($token['expires_in']))
			throw new \RuntimeException('Unexpected response from Google OAuth API');
		$token['created'] = time();
		return $token;
	}
	private function apiCall($method, $url, $body=null, $expectedStatusCode=200, $header=null) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		#curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
			$header===null ? [
				'Authorization: Bearer '.$this->aquireAccessToken(),
				'Content-Type: application/json',
			] : $header
		);

		$response = curl_exec($ch);
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$json = json_decode($response, true);

		if($expectedStatusCode && $statusCode !== $expectedStatusCode) {
			$errorMessage = $response;
			if(!empty($json) && !empty($json['error']['message'])
			&& is_string($json['error']['message']))
				$errorMessage = $json['error']['message'];
			throw new \RuntimeException('Unexpected status code '.$statusCode.': '.$errorMessage);
		}

		return $json;
	}

	function checkPlayStore($token, $appId, $productId) {
		try {
			$purchase = $this->apiCall('GET',
				'https://androidpublisher.googleapis.com/androidpublisher/v3/applications/'.urlencode($appId).'/purchases/subscriptionsv2/tokens/'.urlencode($token)
			);
			foreach(($purchase['lineItems'] ?? []) as $item) {
				if(($item['productId'] ?? null) === $productId) {
					$expiryTimeUnix = strtotime($item['expiryTime'] ?? 0);
					return (time() < $expiryTimeUnix);
				}
			}
		} catch(\RuntimeException $e) {
			error_log($e->getMessage());
		}
		return false;
	}

}
