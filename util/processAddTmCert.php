<?php

require_once '../class/Process.php';

$lnk = new Process();

$certID     = isset($_POST['certID']) ? $_POST['certID'] : null;
$empID      = isset($_POST['empID']) ? $_POST['empID'] : null;
$certIssuer = isset($_POST['certIssuer']) ? $_POST['certIssuer'] : null;
$certName   = isset($_POST['certName']) ? $_POST['certName'] : null;
$certAcq    = isset($_POST['certAcq']) ? $_POST['certAcq'] : null;

$acqDate = $certAcq ? new DateTime($certAcq) : null;
$expDate = $acqDate ? $acqDate->modify('+5 years')->format('m/d/Y') : null;

$insertSql = "INSERT INTO staffing.tmCerts (empID, certID, certName, certIssuer, certAcq, certExp) VALUES (?, ?, ?, ?, ?, ?)";
$insertParams = [$empID, $certID, $certName, $certIssuer, $certAcq, $expDate];
$insertQry = $lnk->query($insertSql, $insertParams);

if ($insertQry) {
	echo "processed";
} else {
	echo "error";
}