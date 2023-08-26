<?php

session_start();
#var_dump($_POST);
require_once("../class/Process.php");

$execID     = isset($_POST['execID']) ? $_POST['execID'] : null;
$execPos    = isset($_POST['execPos']) ? $_POST['execPos'] : null;
$statusID   = isset($_POST['statusID']) ? $_POST['statusID'] : null;
$statusName = isset($_POST['statusName']) ? $_POST['statusName'] : null;
$posID      = isset($_POST['posID']) ? $_POST['posID'] : null;
$classID    = isset($_POST['classID']) ? $_POST['classID'] : null;
$bNum       = isset($_POST['bNum']) ? $_POST['bNum'] : null;

$lnk = new Process();

if ($execID) {
	$qry                             = $lnk->query("SELECT concat(fName, ' ', lName) as execName FROM branchInfo.opsExecs WHERE regionID = " . $execID);
	$_SESSION['filters']['execName'] = $qry[0]['execName'];
	switch ($execPos) {
		case 'reg':
			$_SESSION['filters']['regional'] = "regional = " . $execID;
			$_SESSION['filters']['director'] = "director IS NOT NULL";
			break;
		case 'dir':
			$_SESSION['filters']['regional'] = "regional IS NOT NULL";
			$_SESSION['filters']['director'] = "director = " . $execID;
			break;
		case 'cooMW':
		case 'vpMW':
			$_SESSION['filters']['regional'] = "regional IS NOT NULL";
			$_SESSION['filters']['director'] = "director IS NOT NULL";
			$_SESSION['filters']['location'] = "location = 'MW'";
			break;
		case 'cooEC':
			$_SESSION['filters']['regional'] = "regional IS NOT NULL";
			$_SESSION['filters']['director'] = "director IS NOT NULL";
			$_SESSION['filters']['location'] = "location = 'EC'";
			break;
		default:
			$_SESSION['filters']['regional'] = "regional IS NOT NULL";
			$_SESSION['filters']['director'] = "director IS NOT NULL";
			$_SESSION['filters']['location'] = "location != 'WC'";
	}
}

if ($statusID) {
	if ($statusID != 1) {
		$_SESSION['filters']['status'] = "posStatus = '" . $statusName . "'";
	}
	else {
		$_SESSION['filters']['status'] = "posStatus IS NOT NULL";
	}
}

if ($posID) {
	if ($posID != 183) {
		$_SESSION['filters']['position'] = "position = " . $posID;
	}
	else {
		$_SESSION['filters']['position'] = "position IS NOT NULL";
	}
	$_SESSION['filters']['posClass'] = null;
}

if ($classID) {

	if ($classID != 999) {
		$classSql  = "SELECT classAbr, className FROM staffing.posClasses WHERE classID = " . $classID;
		$classQry  = $lnk->query($classSql);
		$classAbr  = $classQry ? $classQry[0]['classAbr'] : null;
		$className = $classQry ? $classQry[0]['className'] : null;
		$posArr    = [];
		if ($classAbr) {
			$posSql = "SELECT posID FROM staffing.positions WHERE posClass = '" . $classAbr . "'";
			$posQry = $lnk->query($posSql);

			foreach ($posQry as $key => $pos) {
				$posArr[] = $pos['posID'];
			}

			$posStr = implode(', ', $posArr);
		}
	}
	else {
		$className = "All";
		$posStr    = null;
	}

	$_SESSION['filters']['position'] = isset($posStr) ? 'position IN (' . $posStr . ')' : 'position IS NOT NULL';
	$_SESSION['filters']['posClass'] = $className;
}

if ($bNum) {
	$_SESSION['filters']['branch']       = [];
	$_SESSION['filters']['branchString'] = $bNum;

	$brQry = $lnk->query("SELECT branchNum, branchName FROM branchInfo.branches WHERE branchNum IN (" . $bNum . ")");

	foreach ($brQry as $x) {
		$_SESSION['filters']['branch'][$x['branchNum']] = $x['branchName'];
	}

}

$_SESSION['filters']['execPos'] = $execPos;
if ($execID) {
	$_SESSION['filters']['exec'] = $execID;
}

#var_dump($_SESSION['filters']);
var_dump($_SESSION);
