<?php

session_start();

require_once  '../class/Process.php';
require_once '../util/cleanInput.php';

$lnk = new Process();

$newPin = isset($_POST['newPin']) ? clean($_POST['newPin']) : null;
$execID = isset($_POST['execID']) ? clean($_POST['execID']) : null;

if ($newPin && $execID) {
	$updateSql = "UPDATE branchInfo.opsExecs SET pin = ? WHERE regionID = ?";
	$updateParams = [$newPin, $execID];
	$updateQry = $lnk->query($updateSql, $updateParams);

	if ($updateQry) {
		$_SESSION['execData']['pin'] = $newPin;
		echo "Updated Exec Pin";
	} else {
		echo "did not update exec pin";
	}

}
