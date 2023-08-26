<?php

function clean($value, $field = null) {

	// replaces strings < 3 with a '-'
	if (strlen($value) < 3 & !is_numeric($value)) {
		$value = "-";
	} else {
		// If magic quotes not turned on add slashes.
		if (!get_magic_quotes_gpc()) {$value = addslashes($value);}

		// Strip any tags from the value.
		$value = strip_tags($value);

		// replaces extra spaces & tabs & new lines with single space
		$value = preg_replace('/\s+/', ' ', $value);
		$value = preg_replace('/\t+/', ' ', $value);
		$value = preg_replace('/\n\r+/', ' ', $value);

		//converts to iso-8859 format
		#$value = utf8_decode($value);

		// trims white spaces from beginning & end
		$value = trim($value);
	}
	// Return the value out of the function.
	return $value;
}

$sentData = isset($_POST['input']) ? $_POST : null;
if (!is_null($sentData)) {
	foreach ($sentData['input'] as $field => $input) {
		$sentData['input'][$field] = clean($input);
	}
	echo json_encode($sentData);
}
