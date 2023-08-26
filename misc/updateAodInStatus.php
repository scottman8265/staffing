<?php

require_once '../class/Process.php';

$lnk = new Process();

$totalQry = $lnk->query("SELECT aodID, aodEmpID, aodDateIn, outStatus FROM staffing.aodData WHERE aodDateIn > '2020-12-31' && inStatus = 'n'");
$count = 1;
$totalRows = $lnk->getQryCount();

echo "Total Rows: " . $totalRows ."</br>";

foreach($totalQry as $qry) {
	$lineID = $qry['aodID'];
	$empID = $qry['aodEmpID'];
	$dateIn = $qry['aodDateIn'];
	$outStatus = $qry['outStatus'];

	$searchSql = "SELECT * FROM staffing.aodData WHERE aodEmpID = ? && aodDateIn < ?";
	$searchParams = [$empID, $dateIn];
	$searchQry = $lnk->query($searchSql, $searchParams);

	if($searchQry) {
		$lnk->query("UPDATE staffing.aodData SET inStatus = 'c' WHERE aodID = " . $lineID);

	}
	$count++;
	echo $count . " of " . $totalRows . "(" . abs(($count/$totalRows)*100). "%)</br>";
}
