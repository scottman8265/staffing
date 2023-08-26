<?php

session_start();

require_once '../class/Process.php';
require_once '../vendor/autoload.php';

$branchStr = isset($_SESSION['filters']['branchString']) ? $_SESSION['filters']['branchString'] : null;
$branchCnt = isset($_SESSION['filters']['branch']) ? count($_SESSION['filters']['branch']) : 0;

$lnk = new Process();

$openSql       = "SELECT * FROM staffing.branchStaffing WHERE branchNum IN (" . $branchStr . ")  && posStatus = 'Open'";
$upgradeSql    = "SELECT * FROM staffing.branchStaffing WHERE branchNum IN (" . $branchStr . ") && posStatus = 'Need Upgrade'";
$trainingSql   = "SELECT * FROM staffing.branchStaffing WHERE branchNum IN (" . $branchStr . ") && posStatus = 'In Training'";
$promotableSql = "SELECT * FROM staffing.branchStaffing WHERE branchNum IN (" . $branchStr . ") && posStatus = 'Promotable'";
$leaveSql      = "SELECT * FROM staffing.branchStaffing WHERE branchNum IN (" . $branchStr . ") && posStatus = 'On Leave'";
$posSql        = "SELECT * FROM staffing.branchStaffing WHERE branchNum IN (" . $branchStr . ")";
$posQry        = $lnk->query($posSql);
$posCnt        = $lnk->getQryCount();
$openQry       = $lnk->query($openSql);
$openCnt       = $lnk->getQryCount();
$upgradeQry    = $lnk->query($upgradeSql);
$upgradeCnt    = $lnk->getQryCount();
$trainingQry   = $lnk->query($trainingSql);
$trainingCnt   = $lnk->getQryCount();
$promotableQry = $lnk->query($promotableSql);
$promotableCnt = $lnk->getQryCount();
$leaveQry      = $lnk->query($leaveSql);
$leaveCnt      = $lnk->getQryCount();

echo json_encode(['opens'       => $openCnt,
                  'upgrades'    => $upgradeCnt,
                  'training'   => $trainingCnt,
                  'positions'  => $posCnt,
                  'branches'   => $branchCnt,
                  'leaves'      => $leaveCnt,
                  'promotable' => $promotableCnt]);