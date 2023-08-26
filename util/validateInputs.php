<?php

$needProcess = true;
$name = "S'CoTT T-Ayl0r";

if ($needProcess) {
	require_once '../class/Process.php';
}

function validateName($name) {
	$numbers = preg_match_all('/[0-9]/', $name);
	$char = preg_replace('/[^a-zA-Z\t]/', " ", $name);
	$name = ucwords(strtolower($char));
	return $name;
}

validateName($name);