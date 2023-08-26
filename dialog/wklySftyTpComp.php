<?php

session_start();

require_once '../class/Process.php';
require_once '../class/Arrays.php';

$arrs = new Arrays();

$safetyTipCompliance = unserialize($_SESSION['wklySafetyTips']);

$branches = implode(', ', array_keys($safetyTipCompliance));

$arrs->setTeamMemberArray($branches);
$arrs->setBranchArray($branches);

$wkNum = $arrs->getSftyTpWkNum();

var_dump($safetyTipCompliance);



