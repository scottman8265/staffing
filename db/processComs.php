<?php

session_start();

require_once '../class/Process.php';
require_once '../vendor/autoload.php';

#var_dump($_POST);

$lnk = new Process();

$loggedSql    = "SELECT axsEmpID, axsName, axsLevel FROM staffing.dbAccess WHERE axsSession = ?";
$loggedParams = [session_id()];
$getLoggedIn  = $lnk->query($loggedSql, $loggedParams);

$comByID   = isset($getLoggedIn[0]['axsEmpID']) ? $getLoggedIn[0]['axsEmpID'] : null;
$comByName = isset($getLoggedIn[0]['axsName']) ? $getLoggedIn[0]['axsName'] : null;
$comLevel  = isset($getLoggedIn[0]['axsLevel']) ? $getLoggedIn[0]['axsLevel'] : null; #can only be 'branch', 'exec'
$comType   = isset($_POST['comType']) ? $_POST['comType'] : null; #can only be 'pos' or 'emp'
$execOnly  = isset($_POST['execOnly']) ? $_POST['execOnly'] : 'wtf'; #boolean
$comTblId  = isset($_POST['tblID']) ? $_POST['tblID'] : null;
$comEmpId  = isset($_POST['empID']) ? $_POST['empID'] : null;
$comment   = isset($_POST['input']['comment']) ? $_POST['input']['comment'] : null;
$comClass  = isset($_POST['comClass']) ? $_POST['comClass'] : null; #can only be 'new', 'reply', 'edit', 'del'
$comOrigID = isset($_POST['origComID']) ? $_POST['origComID'] : 0;
$comID     = isset($_POST['comID']) ? $_POST['comID'] : null;

echo '[execOnly: ' .$execOnly.'][$_POST[execOnly]: '. $_POST['execOnly']."]";

$comByName = preg_replace('/\s/', '_', $comByName);

$tblArr = ['comByID'   => $comByID,
           'comByName' => $comByName,
           'comLevel'  => $comLevel,
           'comType'   => $comType,
           'execOnly'  => $execOnly,
           'comTblId'  => $comTblId,
           'comEmpId'  => $comEmpId,
           'comment'   => $comment,
           'comClass'  => $comClass,
           'comOrigID' => $comOrigID];

if (!is_null($comClass)) {
	switch ($comClass) {
		case 'new':
		case 'reply':
			newComment($tblArr, $lnk);
			break;
		case 'edit':
			editComment($tblArr, $lnk);
			break;
		case 'del':
			deleteComment($comID, $lnk);
			break;
		default:
			echo "there's a problem here [processComs.php line 53]";
	}
}

/**
 * @param $arr array
 * @param $lnk Process
 */
function newComment($arr, $lnk) {

	foreach ($arr as $col => $val) {

		if (!is_numeric($val) && !is_bool($val)) {
			$val = "'" . $val . "'";
		}

		isset($columns) ? $columns .= ", " . $col : $columns = $col;
		isset($values) ? $values .= ", " . $val : $values = $val;
	}

	$insertSql = "INSERT INTO staffing.tmComments (" . $columns . ") VALUES (" . $values . ")";

	#echo $insertSql;

	$lnk->query($insertSql);
}

/**
 * @param $comID integer
 * @param $lnk   Process
 */
function deleteComment($comID, $lnk) {

	$delSql = "UPDATE staffing.tmComments SET comActive = false WHERE comID = " . $comID;

	echo $delSql;

	$lnk->query($delSql);

}

/**
 * @param $arr array
 * @param $lnk Process
 */
function editComment($arr, $lnk) {


}

/**
 * @param $arr array
 * @param $lnk Process
 */
function replyComment($arr, $lnk) {


}