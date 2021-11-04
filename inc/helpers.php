<?php

/**
 * @param $string mixed
 * @return bool
 */
function isJSON($string) {
  return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
}

/**
 *
 * @param $data object|null
 * @return object
 * @throws Exception
 */
function removeDataNotPresentInRecordType($data) {
	if ($data === null) {
		throw new Exception('No data provided');
	}
	if (!isset($data->type) || empty($data->type)) {
		throw new Exception('Record type not provided');
	}
	$messages = [];
	$record_type_fields = DNS_RECORD_TYPES[$data->type];
	foreach ($data as $key => $value) {
		if (!in_array($key, $record_type_fields)) {
			if (!empty($data->$key)) {
				array_push($messages, 'Field "' . $key . '" is not available for record of type ' . $data->type);
			}
			unset($data->$key);
		}
	}

	return (object) ['data' =>$data, 'messages' => $messages];
}
