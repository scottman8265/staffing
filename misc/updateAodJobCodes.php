<?php

require_once '../class/Process.php';

$lnk = new Process();

$file = fopen('../io/input/aodJobCodesFromRS.csv', "r");

$count = 0;
$jobs  = [];

while (!feof($file)) {
	$combLine = fgets($file);
	$line     = explode(',', $combLine);
	$jobCode  = trim($line[0]);
	$jobName  = isset($line[1]) ? trim($line[1]) : null;
	if ($jobName && strlen($jobCode) > 0 && $count !== 0) {
		$jobs[$jobName] = $jobCode;
	}

	$count ++;
}
fclose($file);

foreach ($jobs as $name => $code) {
	$find = $lnk->query("SELECT id FROM staffing.aodPosTbl WHERE aodPosJobTitle = '" . $name ."'");

	if ($find) {
		$lineID = $find[0]['id'];
		$update = $lnk->query("UPDATE staffing.aodPosTbl set aodPosJobID = ? WHERE id = ?", [$code, $lineID]);
		if ($update) {
			echo "Updated " . $name . "</br>";
		}
		else {
			echo "***Not Updated*** " . $name . "</br>";
		}
	}
	else {
		$insert = $lnk->query("INSERT INTO staffing.aodPosTbl (aodPosJobID, aodPosJobTitle) VALUES (?, ?)", [$code,
		                                                                                                     $name]);

		if ($insert) {
			echo "Inserted " . $name ."</br>";
		} else {
			echo "***Not Inserted*** " . $name . "</br>";
		}
	}

}

var_dump($jobs);

