<?php
/**
 * Implementation of the websupport DNS Management REST API
 */

class WS_DNS_API
{
	const API_URL_BASE = 'https://rest.websupport.sk';

	/**
	 * @param $method string
	 * @param $path   string
	 * @param $data   object|null
	 * @return bool|string
	 * @throws Exception
	 */
	function makeApiRequest(string $method, string $path, object $data = null)	{
		if (empty($method)) {
			throw new Exception('Request type is not set');
		}
		if (!in_array($method, ['GET', 'POST', 'DELETE'])) {
			throw new Exception('Invalid request method "' . $method . '"');
		}
		if (!defined('API_KEY')) {
			throw new Exception('API_KEY is not set');
		}
		if (!defined('API_KEY_SECRET')) {
			throw new Exception('API_KEY_SECRET is not set');
		}

		$time = time();
		$url = self::API_URL_BASE . $path;

		$canonicalRequest = sprintf('%s %s %s', $method, $path, $time);
		$signature = hash_hmac('sha1', $canonicalRequest, API_KEY_SECRET);

		$curl = curl_init();

		switch ($method) {
			case 'POST':
				curl_setopt($curl, CURLOPT_POST, 1);
				if ($data != null) {
					curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
				}
				break;
			case 'DELETE':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
			default:
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
				if ($data != null) {
					$url = sprintf("%s?%s", $url, http_build_query($data));
				}
				break;
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, API_KEY . ':' . $signature);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Date: ' . gmdate('Ymd\THis\Z', $time),
		]);
		$result = curl_exec($curl);
		$curl_error = curl_error($curl);
		if (!empty($curl_error)) {
			throw new Exception('Curl error: ' . $curl_error);
		}
		curl_close($curl);
		return $result;
	}

	function getAllRecords($domain_name = '')	{
		if (empty($domain_name)) {
			$domain_name = DEFAULT_DOMAIN;
		}

		try {
			return (object) [
				'response' => self::makeApiRequest('GET', '/v1/user/self/zone/' . $domain_name . '/record')
			];
		} catch (Exception $e) {
			return $e;
		}
	}

	function addRecord($domain_name = '', $data = null)	{
		if (empty($domain_name)) {
			$domain_name = DEFAULT_DOMAIN;
		}

		$messages = [];

		try {
			$removeDataNotPresentInRecordType = removeDataNotPresentInRecordType($data);
			$data = $removeDataNotPresentInRecordType->data;
			$messages = $removeDataNotPresentInRecordType->messages;
		} catch (Exception $e) {
			return $e;
		}

		try {
			return (object) [
				'response' => self::makeApiRequest('POST', '/v1/user/self/zone/' . $domain_name . '/record', $data),
				'messages' => $messages
			];
		} catch (Exception $e) {
			return $e;
		}
	}

	/**
	 * @throws Exception
	 */
	function removeRecord($domain_name = '', $record_id = null)	{
		if (empty($domain_name)) {
			$domain_name = DEFAULT_DOMAIN;
		}

		if (empty($record_id)) {
			throw new Exception('Record id to delete was not provided');
		}

		try {
			return (object) [
				'response' => self::makeApiRequest('DELETE', '/v1/user/self/zone/' . $domain_name . '/record/' . $record_id)
			];
		} catch (Exception $e) {
			return $e;
		}
	}
}
