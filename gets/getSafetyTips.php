<?php

session_start();

require_once '../class/Process.php';
require_once '../class/Arrays.php';

$arrays = new Arrays();

$arrays->setBranchArray();
$arrays->setTeamMemberArray();

if (isset($_SESSION['filters']['branchString'])) {
	$branches = $_SESSION['filters']['branchString'];
} else {
	$branches = isset($_POST['bNum']) && !is_null($_POST['bNum']) ? "'" . strval($_POST['bNum']) . "'" : '110, 114, 547, 808';
}
function getBranchData($branches) {
	$lnk = new Process();
	$arr = [];
	$sql = "SELECT * FROM staffing.wklySafetyTips WHERE bNum IN (".$branches.") ORDER BY bNum ASC, weekNum ASC";
	$qry = $lnk->query($sql);

	foreach ($qry as $x) {
		$arr[$x['bNum']][$x['weekNum']] = ['empID'=>$x['empID'], 'comments'=>$x['comments'], 'time'=>$x['compTime']];
	}

	return $arr;
}

$today = new DateTime();
$dayOfWeek = $today->format('w');
switch ($dayOfWeek) {
	case 5:
	case 6:
	case 0:
		$wkNum = $today->format("W");
		break;
	default:
		$wkNum = $today->format("W") - 1;
}

$branchArray = getBranchData($branches);

$branchCount = count($branchArray);
$weekCount = 0;

foreach($branchArray as $x) {
	$weekCount += count($x);
}

$required = $branchCount * $wkNum;
$notComplete = $required - $weekCount;
$percentComp = number_format(($weekCount/$required)*100, 2);

$_SESSION['wklySafetyTips'] = serialize($branchArray);

echo json_encode(['notCompliant'=>$notComplete, 'compPercent'=>$percentComp]);