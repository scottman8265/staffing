<?php

require ("../class/Process.php");

$lnk = new Process();

$retrieveSql = "SELECT regionID, eMail FROM branchInfo.opsExecs";
$retrieveQry = $lnk->query($retrieveSql);

foreach ($retrieveQry as $k => $v) {
	$id = $v['regionID'];
	$eMail = $v['eMail'];

	$login = explode('@', $eMail);

	echo "[id: " . $id . "] [login: " . $login[0] . "]</br>";

	$updateSql = "UPDATE branchInfo.opsExecs SET login = '" . $login[0] . "' WHERE regionID = " . $id;
	echo "[updateSql: " . $updateSql . "]</br>";
	$updateQry = $lnk->query($updateSql);

	if ($updateQry) {
		echo "Updated Login For " . $eMail . "</br>";
	} else {
		echo "<strong>Update Failed For " . $eMail . "</strong></br>";
	}
}
