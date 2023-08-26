<?php /** @noinspection PhpUndefinedClassInspection */

session_start();

$_SESSION['counts']['branches']  = 0;

require_once("../class/Process.php");

$lnk        = new Process();
$z          = [];
$temp       = [];
$bVerifySql = null;
#$location   = isset($_SESSION['filters']['location']) ? $_SESSION['filters']['location'] : "location != 'WC'";
$location   = isset($_SESSION['filters']['location']) ? $_SESSION['filters']['location'] : "location != 'WC'";
$regional   = isset($_SESSION['filters']['regional']) ? $_SESSION['filters']['regional'] : "regional IS NOT NULL";
$director   = isset($_SESSION['filters']['director']) ? $_SESSION['filters']['director'] : "director IS NOT NULL";

$_SESSION['testHTML'] = $_POST;

$bVerifySql = "SELECT branchName, branchNum FROM branchInfo.branches WHERE " . $location . " AND " . $regional . " AND " . $director;
echo "<p>" .$bVerifySql."</p>";
$bVerifyQry = $lnk->query($bVerifySql);
foreach ($bVerifyQry as $x => $y) {
	$_SESSION['counts']['branches'] ++;

	$branchNum = $y['branchNum'];

	$z[$branchNum] = $y['branchName'];
	$temp[]        = $branchNum;
}

ksort($z);

$_SESSION['filters']['branch']       = $z;
$branchString = implode(', ', $temp);
$_SESSION['filters']['branchString'] = $branchString;
$_SESSION['execData']['branchString'] = $branchString;


var_dump($_SESSION);
#var_dump($bVerifyQry);




