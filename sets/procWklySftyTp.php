<?php

session_start();

require_once '../class/Process.php';
require_once '../util/cleanInput.php';

function chkOldData($bNum, $wkNum) {
	$lnk = new Process();
	$sql = "SELECT * FROM staffing.wklySafetyTips WHERE bNum = ? && weekNum = ?";
	$params = [$bNum, $wkNum];
	$qry = $lnk->query($sql, $params);

	return $qry ? true : false;
}

$lnk = new Process();

$bNum = isset($_SESSION['branchData']) ? $_SESSION['branchData']['tmLoc'] : null;
$wkNum = isset($_POST['wkNum']) ? clean($_POST['wkNum']) : null;
$comm = isset($_POST['comm']) ? clean($_POST['comm']) : null;
$empID = isset($_SESSION['branchData']) ? $_SESSION['branchData']['tmID'] : null;

$oldData = chkOldData($bNum, $wkNum);

if ($oldData) {
	$sql = "UPDATE staffing.wklySafetyTips SET empID = ?, comments = ? WHERE bNum = ? && weekNum = ?";
	$params = [$empID, $comm, $bNum, $wkNum];
} else {
	$sql = "INSERT INTO staffing.wklySafetyTips (bNum, empID, weekNum, comments) VALUES (?, ?, ?, ?)";
	$params = $bNum && $wkNum && $comm && $empID ? [$bNum, $empID, $wkNum, $comm] : null;
}

$qry = $params ? $lnk->query($sql, $params) : null;

if ($qry) {
	echo "wkly sfty tp processed";
} else {
	echo "wkly sfty tp not processed";
}


