<?php /** @noinspection PhpUndefinedClassInspection */

require ("../class/Process.php");

$lnk = new Process();
$branchInfo = [];
$execs = [];

$branchSql = "SELECT branchNum, branchName, altName, regional, _2DigNum twoDig, director FROM branchInfo.branches WHERE active = 1 AND location <> 'WC'";
$branchQry = $lnk->query($branchSql);

$execSql = "SELECT CONCAT(fName, ' ', lName) AS fullName, regionID, position from branchInfo.opsExecs WHERE active = 1";
$execQry = $lnk->query($execSql);

foreach ($execQry as $y) {
	$execs[$y['regionID']] = $y['fullName'];
}

foreach ($branchQry as $x) {
	if ($x['twoDig']) {
		$branchInfo[$x['twoDig']] = ['branchName'=>$x['branchName'], 'altName'=>$x['altName'], 'regional'=>$execs[$x['regional']], 'director'=>$execs[$x['director']]];
	} else {
		$branchInfo[$x['branchNum']] = ['branchName'=>$x['branchName'], 'altName'=>$x['altName'], 'regional'=>$x['regional'], 'director'=>$x['director']];
	}
}

var_dump($branchInfo);

require '../inc/htmlVars.php';
require '../inc/htmlHeaderTemp.txt';
?>


