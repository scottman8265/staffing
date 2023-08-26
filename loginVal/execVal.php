<?php
session_start();

require_once("../class/Process.php");

$_SESSION['axsLevel'] = 'exec';

function logAccess($name, $pos, $execID) {
	$lnk = new Process();

	$date    = new DateTime();
	$axsDate = $date->format('Y-m-d H:i:s');
	$_SESSION['axsTime'] = $axsDate;

	$sql    = "INSERT INTO staffing.dbAccess (axsName, axsPos, axsLevel, axsDate, axsEmpID, axsModule, axsSession) 
                    VALUES (?, ?, 'exec', ?, ?, 1, ?)";
	$params = [$name, $pos, $axsDate, $execID, session_id()];
	$qry    = $lnk->query($sql, $params);

	$axsID             = $lnk->getLastID();
	$_SESSION['axsID'] = $axsID;

}

function getExecLogIn($login, $pin) {

	$lnk = new Process();

	$sql    = "SELECT regionID as execID, fName, lName, position FROM branchInfo.opsExecs WHERE login = ? AND pin = ?";
	$params = [$login, $pin];
	$qry    = $lnk->query($sql, $params);

	if ($qry) {
		$execID    = $qry[0]['execID'];
		$position  = $qry[0]['position'];
		$execName  = $qry[0]['fName'] . " " . $qry[0]['lName'];
		$firstName = $qry[0]['fName'];

		$_SESSION['execData']            = ['status'       => true,
		                                    'execID'       => $execID,
		                                    'position'     => $position,
		                                    'execName'     => $execName,
		                                    'firstName'    => $firstName,
		                                    'login'        => $login,
		                                    'pin'          => $pin,
		                                    'type'         => 'exec',
		                                    'statusSearch' => 'Open'];
		$_SESSION['filters']['status']   = "posStatus = 'Open'";
		$_SESSION['filters']['execName'] = $execName;
		$_SESSION['filters']['exec'] = $execID;

		switch ($position) {
			case 'reg':
				$_SESSION['filters']['regional'] = "regional = " . $execID;
				break;
			case 'dir':
				$_SESSION['filters']['director'] = "director = " . $execID;
				break;
			case 'cooMW':
				$_SESSION['filters']['location'] = "location = 'MW'";
				break;
			case 'cooEC':
				$_SESSION['filters']['location'] = "location = 'EC'";
				break;
			default:
				$_SESSION['filters']['location'] = "location != 'WC'";
		}

		return ['status'   => true,
		        'execID'   => $execID,
		        'position' => $position,
		        'execName' => $execName,
		        'login'    => $login,
		        'pin'      => $pin];
	} else {
		return ['status' => false];
	}
}

$login = $_POST['login'];
$pin   = $_POST['pin'];

$validation = getExecLogIn($login, $pin);

$validation['status'] ? logAccess($login, $validation['position'], $validation['execID']) : null ;

echo json_encode(['validation' => $validation, 'session' => $_SESSION]);