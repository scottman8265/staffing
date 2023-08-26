<?php /** @noinspection PhpUndefinedClassInspection */

$needProcess = false;
require_once("../vendor/autoload.php");
require_once("../class/Process.php");
require_once  '../util/cleanInput.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

$filename = '../io/input/staffingSheetUpload.csv';
$lnk      = new Process();
// The nested array to hold all the arrays
$x    = [];
$good = $bad = 0;
$profile = [];

// Open the file for reading fills $x array
if (($h = fopen("{$filename}", "r")) !== false) {
	// Each line in the file is converted into an individual array that we call $data
	// The items of the array are comma separated
	while (($data = fgetcsv($h, 1000, ",")) !== false) {
		// Each individual array is being pushed into the nested array
		$x[] = $data;
	}

	// Close the file
	fclose($h);
}


#$params = [];
foreach ($x as $b => $a) {

	$branchNum     = $a[0];
	$position      = $a[1];
	$tmName        = clean($a[2]);
	$empID         = $a[3];
	$dateInPos     = $a[4];
	$posStatus     = $a[5];
	$updated       = $a[6];
	$phoneNum      = $a[7];

	if(!isset($profile[$branchNum][$position])) {
		$profile[$branchNum][$position] = 1;
	} else {
		$profile[$branchNum][$position];
	}

	$sql = "INSERT INTO staffing.branchStaffing (branchNum, position, tmName, empID, dateInPos, posStatus, updated, phoneNum) 
VALUES (?, ?, ?, ?, ? ,?, ?, ?)";
	$params = [$branchNum, $position, $tmName, $empID, $dateInPos, $posStatus, $updated, $phoneNum];

	$qry = $lnk->query($sql, $params);

	if ($qry) {
		echo "inserted branch " . $branchNum . " into database</br>";
	} else {
		echo "***ERROR*** inserting branch " . $branchNum . " into database</br>";
	}
}

foreach ($profile as $brNum => $positions) {
	$prof = serialze($positions);

	$updateSql = "UPDATE branchInfo.branches SET posProfile = ? WHERE branchNum = ?";
	$updateParams = [$prof, $brNum];
	$updateQry = $lnk->query($updateSql, $updateParams);

	if ($updateQry) {
		echo "updated branch profile for branch " . $brNum . "</br>";
	} else {
		echo "***ERROR*** did not update profile for branch " . $brNum . "</br>";
	}

}

