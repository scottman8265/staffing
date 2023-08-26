<?php

require_once '../class/Process.php';

$lnk = new Process();

$lnk->query("UPDATE staffing.branchStaffing set posStatus = 'Need Upgrade' WHERE posStatus = 'Upgrade'");
