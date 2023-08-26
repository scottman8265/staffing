<?php /** @noinspection PhpUndefinedClassInspection */

$needProcess = false;
require_once '../class/Process.php';

$lnk = new Process();

$select = "SELECT tmName, id FROM staffing.branchStaffing";
$selQry = $lnk->query($select);

foreach ($selQry as $k => $v) {

	$name = ucwords(strtolower($v['tmName']));
	$update = "UPDATE staffing.branchStaffing SET tmName = ? WHERE id = ?";
	$updateParams = [$name, $v['id']];
	$updateQry = $lnk->query($update, $updateParams);
}

echo "Updating Complete";