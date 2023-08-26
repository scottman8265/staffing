<?php

session_start();

$axsLevel = $_SESSION['axsLevel'];

require_once '../class/Process.php';
require_once '../vendor/autoload.php';

function getTestArr() {
	$lnk = new Process();

	$arr = [];

	$qry = $lnk->query("SELECT displayName, testName, testID FROM branchInfo.jcmsTestinfo WHERE testID < 16");

	foreach ($qry as $x) {
		$arr[$x['testID']] = ['testName' => $x['testName'], 'displayName' => $x['displayName']];
	}

	return $arr;
}

function getTestsForPositions($tests) {
	$lnk = new Process();
	$arr = [];

	$qry = $lnk->query("SELECT posID, jcmsTests, jcmsFrequency, jcmsGracePeriod FROM staffing.positions");

	foreach ($qry as $x) {
		if (strlen($x['jcmsTests'] > 0)) {
			$testIDs          = explode(":", $x['jcmsTests']);
			$arr[$x['posID']] = ['frequency'   => $x['jcmsFrequency'],
			                     'gracePeriod' => $x['jcmsGracePeriod']];
			foreach ($testIDs as $y) {
				if (isset($tests[$y]['testName'])) {
					$arr[$x['posID']]['tests'][] = $tests[$y]['testName'];
				}
			}
		}
	}

	return $arr;

}

function getStaffingTM($branches, $tests, $aodData) {
	$lnk = new Process();
	$arr = [];

	$qry = $lnk->query("SELECT position, empID, dateInPos, branchNum FROM staffing.branchStaffing WHERE posStatus NOT IN ('Open', 'On Leave') && branchNum IN (" . $branches . ") ORDER BY branchNum");

	foreach ($qry as $x) {

		if (isset($tests[$x['position']])) {
			$dateObj     = new DateTime($x['dateInPos']);
			$gracePeriod = new DateInterval('P' . $tests[$x['position']]['gracePeriod'] . 'D');
			$graceDate   = $dateObj->add($gracePeriod);
			$aodCheck    = checkAodData($x['empID'], $aodData);
			if (substr($x['empID'], 0, 3) != $x['branchNum'] && $aodCheck === true) {
				$arr[$x['empID']] = ['pos'       => $x['position'],
				                     'dateIn'    => $x['dateInPos'],
				                     'graceDate' => $graceDate->format("Y-m-d"),
				                     'branch'    => $x['branchNum']];
			}
		}

	}

	return $arr;
}

function getTestScores($empIDs) {
	$lnk = new Process();
	$arr = [];

	$qry = $lnk->query("SELECT tmID, posDate, test, dateTaken, score FROM staffing.jcmsTests WHERE tmID IN (" . $empIDs . ")");

	foreach ($qry as $x) {
		$arr[$x['tmID']][$x['test']] = ['posDate'   => $x['posDate'],
		                                'dateTaken' => $x['dateTaken'],
		                                'score'     => $x['score']];
	}

	return $arr;

}

function setRequirementArray() {
	return ['compliant' => [], 'notCompliant' => [], 'notRequired' => []];
}

function noSeafood() {

	$arr = [];
	$lnk = new Process();
	$qry = $lnk->query("SELECT branchNum FROM branchInfo.branches WHERE seafood = 0");

	foreach ($qry as $x) {
		$arr[] = $x['branchNum'];
	}

	return $arr;
}

function getAodData($branches, $axsLevel) {
	$lnk = new Process();
	if ($axsLevel === 'exec') {
		$sql = "SELECT aodEmpID FROM staffing.aodData WHERE aodLocation IN (" . $branches . ") && aodDateOut IS NULL";
	} else {
		$sql = "SELECT aodEmpID FROM staffing.aodData WHERE aodLocation = " . $branches . "  && aodDateOut IS NULL";
	}
	#echo $sql;
	#echo $branches;
	$qry = $lnk->query($sql);

	$arr = [];

	foreach ($qry as $x) {
		$arr[] = $x['aodEmpID'];
	}

	return $arr;

}

function checkAodData($empID, $aodData) {
	if (array_search($empID, $aodData) === false) {
		return false;
	} else {
		return true;
	}
}
#echo $_SESSION['filters']['branchString'];
if (isset($_SESSION['filters']['branchString'])) {
	$branches = $_SESSION['filters']['branchString'];
} else {
	$branches = isset($_POST['bNum']) && !is_null($_POST['bNum']) ? "'" . strval($_POST['bNum']) . "'" : '110, 114, 547, 808';
}
#echo $branches;
$testArr           = getTestArr();
$aodData           = getAodData($branches, $axsLevel);
$testsForPositions = getTestsForPositions($testArr);
$staffingTM        = getStaffingTM($branches, $testsForPositions, $aodData);

$branchTestsTaken  = getTestScores(implode(", ", array_keys($staffingTM)));
$noSeafood         = noSeafood();



$today = new DateTime('today');

$reqCnt       = $compCnt = $notComp = $notReq = 0;
$byTeamMember = $byBranch = $byTest = [];


foreach ($staffingTM as $empID => $a) {

	$reqTests   = $testsForPositions[$a['pos']]['tests'];
	$dateIn     = new DateTime($a['dateIn']);
	$graceDate  = new DateTime($staffingTM[$empID]['graceDate']);
	$testsTaken = isset($branchTestsTaken[$empID]) ? $branchTestsTaken[$empID] : [];
	$frequency  = new DateInterval("P" . $testsForPositions[$a['pos']]['frequency'] . "D");
	$branch     = $a['branch'];

	$noSeafoodDept = array_search($branch, $noSeafood);
	$seafoodTest   = array_search('Seafood', $reqTests);

	#echo $noSeafoodDept ."</br>";

	if ($noSeafoodDept !== false && $seafoodTest !== false) {
		#echo $branch . "</br>";
		unset ($reqTests[$seafoodTest]);
	}

	if (!isset($byTeamMember[$empID])) {
		$byTeamMember[$empID] = setRequirementArray();
	}
	if (!isset($byBranch[$branch])) {
		$byBranch[$branch] = setRequirementArray();
	}

	foreach ($reqTests as $testName) {

		if (!isset($byTest[$testName])) {
			$byTest[$testName] = setRequirementArray();
		}

		$reqCnt ++;

		if (isset($testsTaken[$testName]) && !empty($testsTaken)) {
			$dateTaken    = new DateTime($testsTaken[$testName]['dateTaken']);
			$dateTakenStr = $dateTaken->format("Y-m-d");
			$dueDate      = $dateTaken->add($frequency);
			$score        = $testsTaken[$testName]['score'];

			if ($dueDate < $today || $score < 84.5) {
				$notComp ++;
				$byTeamMember[$empID]['notCompliant'][$testName] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                    'dateTaken' => $dateTakenStr,
				                                                    'dueDate'   => $dueDate->format("Y-m-d"),
				                                                    'score'     => $score];

				$byBranch[$branch]['notCompliant'][$empID][$testName] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                         'dateTaken' => $dateTakenStr,
				                                                         'dueDate'   => $dueDate->format("Y-m-d"),
				                                                         'score'     => $score];

				$byTest[$testName]['notCompliant'][$branch][$empID] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                       'dateTaken' => $dateTakenStr,
				                                                       'dueDate'   => $dueDate->format("Y-m-d"),
				                                                       'score'     => $score];
			} else {
				$compCnt ++;
				$byTeamMember[$empID]['compliant'][$testName] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                 'dateTaken' => $dateTakenStr,
				                                                 'dueDate'   => $dueDate->format("Y-m-d"),
				                                                 'score'     => $score];

				$byBranch[$branch]['compliant'][$empID][$testName] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                      'dateTaken' => $dateTakenStr,
				                                                      'dueDate'   => $dueDate->format("Y-m-d"),
				                                                      'score'     => $score];

				$byTest[$testName]['compliant'][$branch][$empID] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                    'dateTaken' => $dateTakenStr,
				                                                    'dueDate'   => $dueDate->format("Y-m-d"),
				                                                    'score'     => $score];
			}
			unset($testsTaken[$testName]);
		} else {
			if ($graceDate < $today) {
				$notComp ++;
				$byTeamMember[$empID]['notCompliant'][$testName] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                    'dateTaken' => "N/A",
				                                                    'dueDate'   => $graceDate->format("Y-m-d"),
				                                                    'score'     => 'N/A'];

				$byBranch[$branch]['notCompliant'][$empID][$testName] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                         'dateTaken' => 'N/A',
				                                                         'dueDate'   => $graceDate->format("Y-m-d"),
				                                                         'score'     => 'N/A'];

				$byTest[$testName]['notCompliant'][$branch][$empID] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                       'dateTaken' => 'N/A',
				                                                       'dueDate'   => $graceDate->format("Y-m-d"),
				                                                       'score'     => 'N/A'];
			} else {
				$compCnt ++;
				$byTeamMember[$empID]['compliant'][$testName] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                 'dateTaken' => "N/A",
				                                                 'dueDate'   => $graceDate->format("Y-m-d"),
				                                                 'score'     => 'N/A'];

				$byBranch[$branch]['compliant'][$empID][$testName] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                      'dateTaken' => 'N/A',
				                                                      'dueDate'   => $graceDate->format("Y-m-d"),
				                                                      'score'     => 'N/A'];

				$byTest[$testName]['compliant'][$branch][$empID] = ['dateIn'    => $dateIn->format("Y-m-d"),
				                                                    'dateTaken' => 'N/A',
				                                                    'dueDate'   => $graceDate->format("Y-m-d"),
				                                                    'score'     => 'N/A'];
			}
		}
	}

	foreach ($testsTaken as $testName => $b) {
		$byTeamMember[$empID]['notRequired'][$testName] = ['dateIn'    => $dateIn->format("Y-m-d"),
		                                                   'dateTaken' => $b['dateTaken'],
		                                                   'dueDate'   => 'N/A',
		                                                   'score'     => $b['score']];

		$byBranch[$branch]['notRequired'][$empID][$testName] = ['dateIn'    => $dateIn->format("Y-m-d"),
		                                                        'dateTaken' => $b['dateTaken'],
		                                                        'dueDate'   => 'N/A',
		                                                        'score'     => $b['score']];

		$byTest[$testName]['notRequired'][$branch][$empID] = ['dateIn'    => $dateIn->format("Y-m-d"),
		                                                      'dateTaken' => $b['dateTaken'],
		                                                      'dueDate'   => 'N/A',
		                                                      'score'     => $b['score']];
	}
}

$_SESSION['jcms'] = ['branch' => serialize($byBranch), 'test' => serialize($byTest), 'tm' => serialize($byTeamMember)];

#var_dump(serialize($byBranch));

echo json_encode(['totalRequired' => $reqCnt,
                  'compliant'     => $compCnt,
                  'notCompliant'  => $notComp,
                  'compPercent'   => number_format(($compCnt / $reqCnt) * 100, 2)]);





