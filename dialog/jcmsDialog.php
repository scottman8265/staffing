<?php

session_start();

require_once '../class/Process.php';
require_once '../vendor/autoload.php';
require_once '../class/Arrays.php';

$jcmsByBranch = isset($_SESSION['jcms']) ? unserialize($_SESSION['jcms']['branch']) : unserialize('a:92:{i:110;a:3:{s:9:"compliant";a:13:{i:47035;a:13:{s:2:"IC";a:4:{s:6:"dateIn";s:10:"2019-01-13";s:9:"dateTaken";s:10:"2020-02-25";s:7:"dueDate";s:10:"2022-02-24";s:5:"score";s:3:"100";}s:5:"Floor";a:4:{s:6:"dateIn";s:10:"2019-01-13";s:9:"dateTaken";s:10:"2020-02-24";s:7:"dueDate";s:10:"2022-02-23";s:5:"score";s:3:"100";}s:9:"Receiving";a:4:{s:6:"dateIn";s:10:"2019-01-13";s:9:"dateTaken";s:10:"2020-02-24";s:7:"dueDate";s:10:"2022-02-23";s:5:"score";s:3:"100";}s:5:"Admin";a:4:{s:6:"dateIn";s:10:"2019-01-13"');

$branchString = implode(', ', array_keys($jcmsByBranch));

$arrays = new Arrays();

$arrays->setBranchArray($branchString);
$arrays->setTeamMemberArray($branchString);
$arrays->setStaffingPosArray();

$count = 0;

foreach ($jcmsByBranch as $branch => $data) {
	echo '<h3 class="jcmsReqBranch">' . $arrays->getBranchName($branch) . ' - ' . count($data['notCompliant']) . '<span class="copy" data-branch="'.$branch.'" data-type="jcmsByBranch">copy</span></h3><div><div class="jcmsAccordion2 jcmsReqTM">';
	$count += count($data['notCompliant']);
	foreach ($data['notCompliant'] as $tmID => $testInfo) {
		echo "<h3>".$arrays->getTmName($tmID)." (".count($testInfo) . ")</br>" . $arrays->getPosName($arrays->getTmPosition($tmID)) . " - " . $arrays->getTmDateInPos($tmID) . "</h3>";
		echo "<div><table class='tmReqTestTbl' style='margin:auto'><thead><th>Test</th><th>Last Taken</th><th>Last Score</th><th>Due Date</th></thead><tbody>";
		foreach ($testInfo as $testName => $y) {
			echo "<tr><td>" . $testName . "</td><td>" . $y['dateTaken'] . "</td><td>" . $y['score'] . "</td><td>" . $y['dueDate'] . "</td></tr>";
		}
		echo "</tbody></table></div>";
	}
	echo "</div></div>";
}


