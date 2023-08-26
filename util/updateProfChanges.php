<?php

session_start();

require_once '../class/Process.php';

$lnk = new Process();

$profile = $_SESSION['brProfUpdate']['profile'];
$bNum = $_SESSION['brProfUpdate']['bNum'];

foreach ($profile as $key => $count) {
	if ($count = 0) {
		unset ($profile[$key]);
	}
}

#var_dump(serialize($profile));


$sql = "UPDATE branchInfo.branches SET posProfile = ? WHERE branchNum = ?";
$params = [serialize($profile), $bNum];
$qry = $lnk->query($sql, $params);