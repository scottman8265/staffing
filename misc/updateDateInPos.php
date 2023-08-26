<?php

require_once '../class/Process.php';
require_once '../vendor/autoload.php';

$lnk = new Process();

$qry = $lnk->query("SELECT id, empID FROM staffing.branchStaffing WHERE dateInPos = '1/1/1900'");

foreach ($qry as $x) {
	$id = $x['id'];
	$empID = $x['empID'];

	$hiredQry = $lnk->query("SELECT aodHiredDate FROM staffing.aodData WHERE aodEmpID = " . $empID);

	if ($hiredQry) {
		$hiredObj = new DateTime($hiredQry[0]['aodHiredDate']);
		$hiredDate = $hiredObj->format('m/d/Y');
		$updateSql = "UPDATE staffing.branchStaffing SET dateInPos = ? WHERE id = ?";
		$updateParams = [$hiredDate, $id];
		$updateQry = $lnk->query($updateSql, $updateParams);
	}

}
