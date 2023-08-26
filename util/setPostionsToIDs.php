<?php

require_once '../class/Process.php';

$lnk = new Process();
$branches = [];
$positions = [];

$sql1 = 'SELECT id, position from staffing.branchStaffing';
$qry1 = $lnk->query($sql1);

foreach ($qry1 as $x => $y) {
	if (strlen($y['position']) > 3) {
		$branches[$y['id']] = $y['position'];
	}
}

$sql2 = 'SELECT posID, posName FROM staffing.positions';
$qry2 = $lnk->query($sql2);
foreach ($qry2 as $x => $y) {
	$positions[$y['posID']] = $y['posName'];
}

foreach ($branches as $id => $position) {
	$posID = array_search($position, $positions);

	$posID ? $sql3 = 'UPDATE staffing.branchStaffing SET position = ? WHERE id = ?' : $sql3 = null;

	!is_null($sql3) ? $params3 = [$posID, $id] : $params3 = null;

	$update = $lnk->query($sql3, $params3);

	if ($update) {
		echo $posID . " WORKED</br>";
	} else {
		echo $posID . " DID NOT WORK</br>";
	}
}
