<?php

require_once '../class/Process.php';

$lnk = new Process();

$qry = $lnk->query("SELECT posID FROM staffing.positions WHERE posClass != 'admin'");

foreach ($qry as $x) {
	echo $x['posID'] . ", ";
}
