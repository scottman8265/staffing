<?php

require_once '../class/Process.php';

$lnk = new Process();

$staffingQry = $lnk->query("SELECT DISTINCT branchNum FROM staffing.branchStaffing WHERE CHAR_LENGTH(branchNum)<3");
$branchQry = $lnk->query("SELECT branchNum, _2DigNum FROM branchInfo.branches WHERE _2DigNum IS NOT NULL");

$branchArr = [];

foreach ($branchQry as $x) {
	$branchArr[$x['_2DigNum']] = $x['branchNum'];
}

/*var_dump($branchArr);
var_dump($staffingQry);*/

foreach($staffingQry as $y) {
	echo $y['branchNum'] . " -> " . $branchArr[$y['branchNum']];

	$updateSql = "UPDATE staffing.branchStaffing SET branchNum = ? WHERE branchNum = ?";
	$updateParams = [$branchArr[$y['branchNum']], $y['branchNum']];
	$updateQry = $lnk->query($updateSql, $updateParams);

	if ($updateQry) {
		echo "  [good]</br>";
	} else {
		echo "  [bad]</br>";
	}

}

