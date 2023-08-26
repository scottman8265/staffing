<?php

session_start();

require_once ('../class/Arrays.php');

$type = isset($_POST['type']) ? $_POST['type'] : null;
$selector = isset($_POST['selector']) ? $_POST['selector'] : null;
$arrays = new Arrays();

$arrays->setBranchArray($selector);
$arrays->setTeamMemberArray($selector);
$arrays->setStaffingPosArray();


$data = null;

switch ($type) {
	case 'jcmsByBranch':
		$rawData = unserialize($_SESSION['jcms']['branch']);
		$data = $rawData[$selector]['notCompliant'];
		break;
	default:
		$data = null;
		break;
}

if (!is_null($data)) {
	echo "<table class='tmReqTestTbl' style='margin:auto'><thead><th>Team Member</th><th>Position</th><th>Date In Pos</th><th>Test</th><th>Last Taken</th><th>Last Score</th><th>Due Date</th></thead><tbody>";
	foreach ($data as $tmID => $testInfo) {
		foreach ($testInfo as $testName => $y) {
			echo "<tr><td>".$arrays->getTmName($tmID)."</td><td>" . $arrays->getPosName($arrays->getTmPosition($tmID)) . "</td><td>" . $arrays->getTmDateInPos($tmID) . "</td>";
			echo "<td>" . $testName . "</td><td>" . $y['dateTaken'] . "</td><td>" . $y['score'] . "</td><td>" . $y['dueDate'] . "</td></tr>";
		}
			}
	echo "</tbody></table>";
}
