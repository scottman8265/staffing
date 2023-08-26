<?php

require_once '../class/Process.php';

$lnk = new Process();

$sql = "SELECT DISTINCT posStatus FROM staffing.branchStaffing";
$qry = $lnk->query($sql);

foreach ($qry as $x) {
	echo $x['posStatus'] . "</br>";
}
