<?php

/**
 * this sets the branch profiles from existing staffing sheets loaded
 * will go through all positions in staffing.branchStaffing to build profile & update each branchInfo.branches(posProfile)
 */

require_once '../class/Process.php';

$lnk = new Process();

$sql = "SELECT branchNum as bNum, position as pos FROM staffing.branchStaffing WHERE branchNum = 431";
$qry = $lnk->query($sql);
$z = [];

foreach ($qry as $x) {
	isset($z[$x['bNum']][$x['pos']]) ? $z[$x['bNum']][$x['pos']]++ : $z[$x['bNum']][$x['pos']] = 1;
}

foreach ($z as $a => $c) {
	$b = serialize($c);
	echo "bNum: " . $a . ":  " . $b . "</br></br>";
	strlen($a) === 2 ? $bNumField = '_2DigNum' : $bNumField = 'branchNum';

	$updateSql = "UPDATE branchInfo.branches SET posProfile = '" .$b . "' WHERE " .$bNumField . " = " .$a;
	$updateQry = $lnk->query($updateSql);
	echo $updateSql . "</br></br>";
}

/*$s = serialize($z);

$u = unserialize($s);

var_dump($u);*/


