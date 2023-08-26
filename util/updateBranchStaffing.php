<?php

session_start();

require_once '../class/Process.php';
require_once '../util/cleanInput.php';

function updateLogTbl($axsID, $field, $table, $id, $original, $update, $updateType, $inReason, $outReason) {
	$lnk = new Process();

	$time       = new DateTime();
	$updateTime = $time->format('Y-m-d H:i:s');

	$updateLogSql = "INSERT INTO staffing.updateLog (axsID, tblName, tblID, field, original, updateTo, sessID, updateType, inReason, outReason, updateTime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	$params       = [$axsID,
	                 $table,
	                 $id,
	                 $field,
	                 $original,
	                 $update,
	                 session_id(),
	                 $updateType,
	                 $inReason,
	                 $outReason,
	                 $updateTime];
	try {
		$qry = $lnk->query($updateLogSql, $params);
	} catch (Exception $e) {
		$_SESSION['errors'][] = 'error in updating update log: ' . $e->getMessage();
	}

	!$qry ? $_SESSION['errors'][] = "not updated, updateType: " . $updateType : null;

	return $qry ? true : false;
}

function updateStaffingSheetTbl($field, $table, $id, $update, $updateDate) {
	$lnk = new Process();

	#echo "[update date in function: " . $updateDate . "] ";

	$updateSql     = "UPDATE " . $table . " SET " . $field . " =  '" . $update . "' WHERE id = " . $id;
	$updateDateSql = "UPDATE " . $table . " SET updated =  '" . $updateDate . "' WHERE id = " . $id;

	$updateCheck     = $lnk->query($updateSql);
	$updateDateCheck = $lnk->query($updateDateSql);

	if ($updateCheck && $updateDateCheck) {
		return true;
	} else {
		return false;
	}

}

$update     = clean($_POST['update']);
$id         = $_POST['id'];
$table      = $_POST['table'];
$field      = $_POST['field'];
$updateDate = $_POST['updateDate'];
$execOnly   = $_POST['execOnly'];
$updateType = $_POST['updateType'];
$inReason   = $_POST['inReason'];
$outReason  = $_POST['outReason'];

#var_dump($_SESSION);

$axsID = isset($_SESSION['axsID']) ? $_SESSION['axsID'] : null;

$getOriginalQry = [];

$lnk = new Process();

$field = $field === 'name' ? 'tmName' : $field;
$field = $field === 'date' ? 'dateInPos' : $field;

$getOriginalSql = "SELECT " . $field . " FROM " . $table . " WHERE id = " . $id;

$getOriginalQry = $lnk->query($getOriginalSql);

$original = $getOriginalQry ? $getOriginalQry[0][$field] : null;

$updateLog              = $original ? updateLogTbl($axsID, $field, $table, $id, $original, $update, $updateType, $inReason, $outReason) : $_SESSION['errors'][] = 'no original in updateLog';
$updateStaffingTblCheck = updateStaffingSheetTbl($field, $table, $id, $update, $updateDate);

$returnArray = ["originalStatus" => $original,
                "execOnly"       => $execOnly,
                "updateLog"      => $updateLog,
                "updateStaffing" => $updateStaffingTblCheck,
                "updateDate"     => $updateDate,
                "field"          => $_POST['field'],
                "update"         => $update,
                "id"             => $id];

isset($_SESSION['errors']) ? $returnArray['errors'] = $_SESSION['errors'] : $returnArray['errors'] = 'no errors';

unset($_SESSION['errors']);

echo json_encode($returnArray);




