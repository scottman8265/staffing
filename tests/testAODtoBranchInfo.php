<?php

require_once '../class/Process.php';

$lnk = new Process();

$aodBranchQry = $lnk->query("SELECT DISTINCT aodLocation as aodBranch FROM staffing.aodData");

$branchInfoQry = $lnk->query("SELECT branchName, branchNum FROM branchInfo.branches");

foreach($branchInfoQry as $x) {
	$branches[$x['branchNum']] = $x['branchName'];
}

foreach ($aodBranchQry as $y) {
	
	$aodBranch = $y['aodBranch'] === '414' ? '114' : $y['aodBranch'];
	
	if (isset($branches[$aodBranch])) {
		#echo $branches[$aodBranch] . "</br>";
	} else {
		echo $aodBranch . " no branches</br>";
	}
}