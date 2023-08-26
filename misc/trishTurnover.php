<?php

/**
 * figures turnover for target branches
 */

require_once '../class/Process.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

/**  gets target branches from branchInfo Table
 * @return array
 */
function getBranches() {
	$arr = [];
	$lnk = new Process();
	$qry = $lnk->query("SELECT branchNum, branchName, regional, director FROM branchInfo.branches WHERE location = 'MW' ORDER BY branchNum DESC");

	foreach ($qry as $x) {
		$arr[$x['branchNum']]['info'] = ['bName' => $x['branchName'],
		                                 'reg'   => $x['regional'],
		                                 'dir'   => $x['director']];
	}

	ksort($arr);

	return $arr;
}

/** gets position codes from aodPosTable
 *
 * @param $mgmtPos
 *
 * @return array
 */
function getPosCodes() {

	$arr = [];
	$lnk = new Process();
	$qry = $lnk->query("SELECT aodPosJobID, aodPosJobTitle, class FROM staffing.aodPosTbl WHERE class != 'c'");

	foreach ($qry as $x) {
		$arr[$x['aodPosJobID']] = ['posName' => $x['aodPosJobTitle'], 'class' => $x['class']];
	}

	return $arr;
}

/** gets staffing from aodData for target branches
 *
 * @param $branches
 *
 * @param $posCodes
 *
 * @return array
 */
function getBranchStaffing($branches, $posCodes) {

	$positions = array_keys($posCodes);
	#$branchArr = array_keys($branches);

	$inPositions = "'" . implode('\', \'', $positions) . "'";
	$branchStr   = "'" . implode('\', \'', $branches) . "'";

	$arr = [];
	$lnk = new Process();

	$qry = $lnk->query("SELECT aodEmpID, aodJobID, concat(aodFname, ' ', aodLname) as teamMem, aodDateIn, aodDateOut, outStatus, aodLocation FROM staffing.aodData WHERE aodLocation IN (" . $branchStr . ") && aodJobID IN (" . $inPositions . ") && aodDateIn > '2020-07-15'");

	foreach ($qry as $x) {
		$arr[$x['aodLocation']][$x['aodJobID']][] = ['empID'     => $x['aodEmpID'],
		                                             'name'      => $x['teamMem'],
		                                             'dateIn'    => $x['aodDateIn'],
		                                             'dateOut'   => $x['aodDateOut'],
		                                             'outStatus' => $x['outStatus'],
		                                             'class'     => $posCodes[$x['aodJobID']]['class']];
	}

	return $arr;
}

function getHeadCount($branches, $posCodes) {

	$lnk = new Process();
	$arr = [];

	$branchStr = "'" . implode('\', \'', $branches) . "'";

	$qry = $lnk->query("SELECT aodLocation, aodJobID FROM staffing.aodData WHERE aodLocation IN (" . $branchStr . ") && aodDateOut IS NULL && aodJobID != 213");

	foreach ($qry as $x) {
		$location = $x['aodLocation'];
		$jobCode  = $x['aodJobID'];
		$class    = $posCodes[$jobCode]['class'];
		$posName  = $posCodes[$jobCode]['posName'];

		if (!isset($arr[$location])) {
			$arr[$location] = ['total' => 0, 'mgmt' => 0, 'cashier' => 0, 'stocker' => 0, 'otherUnion' => 0];
		}

		$arr[$location]['total'] ++;

		if ($class == 'u') {
			if ($jobCode == 'WCAS' || $jobCode == 'WCHE') {
				$arr[$location]['cashier'] ++;
			}
			elseif (substr($posName, 0, 7) == 'Stocker') {
				$arr[$location]['stocker'] ++;
			}
			else {
				$arr[$location]['otherUnion'] ++;
			}
		}
		else {
			$arr[$location]['mgmt'] ++;
		}

	}

	return $arr;
}

function getPosDate($empID, $dateIn) {
	$lnk = new Process();

	$qry = $lnk->query("SELECT posDate FROM staffing.jcmsTests WHERE tmID = ?", [$empID]);

	if ($qry) {
		return $qry[0]['posDate'];
	}
	else {
		return $dateIn;
	}

}

function getBranchDetails($branches, $posCodes, $branchStaffing, $headCount) {
	$readArr   = [];
	$returnArr = [];
	foreach ($branches as $branchNum => $info) {
		foreach ($posCodes as $pos => $data) {
			if (isset($branchStaffing[$branchNum][$pos])) {
				$readArr[$branchNum]['info']           = $branches[$branchNum]['info'];
				$readArr[$branchNum]['staffing'][$pos] = $branchStaffing[$branchNum][$pos];
			}
		}
	}
	ksort($readArr);

	foreach ($readArr as $branchNum => $datax) {
		$bName = $datax['info']['bName'];
		foreach ($datax['staffing'] as $positionsx => $datay) {
			$posName = $posCodes[$positionsx]['posName'];
			$class   = $posCodes[$positionsx]['class'];
			foreach ($datay as $key => $infox) {
				$dateIn      = $infox['dateIn'] == '2020-07-14' || $infox['dateIn'] == '2020-07-15' || $infox['dateIn'] == '2020-07-16' ? getPosDate($infox['empID'], $infox['dateIn']) : $infox['dateIn'];
				$dateInObj   = new DateTime($dateIn);
				$dateOutObj  = strlen($infox['dateOut']) > 5 ? new DateTime($infox['dateOut']) : new DateTime();
				$dateDiff    = $dateInObj->diff($dateOutObj);
				$tenureMonth = $dateDiff->format('%m');
				$tenureYear  = $dateDiff->format('%y');
				$tenure      = ($tenureYear * 12) + $tenureMonth;

				switch ($infox['outStatus']) {
					case 't':
						$outStatus = 'termed';
						break;
					case 'c':
						$outStatus = 'changed';
						break;
					default:
						$outStatus = 'current';
						break;
				}

				$returnArr[$branchNum][] = ['posName'   => $posName,
				                            'empID'     => $infox['empID'],
				                            'empName'   => $infox['name'],
				                            'in'        => $dateIn,
				                            'out'       => $infox['dateOut'],
				                            'outStatus' => $outStatus,
				                            'tenure'    => $tenure,
				                            'class'     => $class];
			}
		}
	}

	return $returnArr;
}

function processDetails($arr) {

	$figureArr = [];
	$returnArr = [];

	foreach ($arr as $bNum => $data) {
		foreach ($data as $key => $info) {
			$figureArr[$bNum][$info['posName']]['tenure'][] = $info['tenure'];

			if (!isset($figureArr[$bNum][$info['posName']]['counts'])) {
				$figureArr[$bNum][$info['posName']]['counts'] = ['current' => 0,
				                                                 'new'     => 0,
				                                                 'changed' => 0,
				                                                 'termed'  => 0];
			}

			if (new DateTime($info['in']) > new DateTime('12/31/2019')) {
				$figureArr[$bNum][$info['posName']]['counts']['new'] ++;
			}

			switch ($info['outStatus']) {
				case 'changed':
					$figureArr[$bNum][$info['posName']]['counts']['changed'] ++;
					break;
				case 'termed':
					$figureArr[$bNum][$info['posName']]['counts']['termed'] ++;
					break;
				case 'current':
					$figureArr[$bNum][$info['posName']]['counts']['current'] ++;
					break;
			}

		}
	}

	foreach ($figureArr as $bNumx => $posNamex) {

		foreach ($posNamex as $posName => $datax) {

			$returnArr[$bNumx][$posName] = ['count'         => count($datax['tenure']),
			                                'averageTenure' => array_sum($datax['tenure']) / count($datax['tenure']),
			                                'current'       => $datax['counts']['current'],
			                                'new'           => $datax['counts']['new'],
			                                'changed'       => $datax['counts']['changed'],
			                                'termed'        => $datax['counts']['termed']];
		}
	}

	return $returnArr;
}

$periods       = ['Q1', 'Q2', 'Q3', 'Q4'];
$auditArr      = ['Total'         => 'total',
                  'Fresh'         => 'fresh',
                  'Admin'         => 'admin',
                  'Cashroom'      => 'cashroom',
                  "Dairy/Freezer" => 'deli',
                  "Floor"         => 'floor',
                  "Front End"     => 'frontEnd',
                  "Gen Ops"       => 'genOps',
                  "IC"            => 'ic',
                  "Meat"          => 'meat',
                  "Pest"          => "pest",
                  "Produce"       => 'produce',
                  "Receiving"     => 'receiving',
                  "Reception"     => 'reception',
                  "Safety"        => 'safety',
                  "Seafood"       => 'seafood',
                  'Smallwares'    => 'smware'];
$invHeaders    = ['Inv Date', 'Inv Count', 'Inv Adj', 'Per Int', 'Net Shrink', 'Shrink %', 'Net Dmg', 'Dmg %'];
$tenureHeaders = ['Position', 'Total Count', 'Current', 'New', 'Changed', 'Termed', 'Avg Tenure'];
$sumHeaders    = ['Branch Num',
                  'Branch Name',
                  'Tot Mgr Cnt',
                  'Curr Mgr Cnt',
                  'New Mgr Cnt',
                  'Changed Mgr Cnt',
                  'Termed Mgr Cnt',
                  'Avg Mgr Tenure',
                  '2020 Audit Avg',
                  'Last Inv Date',
                  'Inv Count',
                  'Inv Adj',
                  'Net Adj',
                  'Shrink %',
                  'Net Dmg',
                  'Dmg %'];

$summary = [];

$branches = getBranches();

$posCodes = getPosCodes();

$headCount = getHeadCount(array_keys($branches), $posCodes);

$branchStaffing = getBranchStaffing(array_keys($branches), $posCodes);

$branchDetails = getBranchDetails($branches, $posCodes, $branchStaffing, $headCount);

#$turnOver = processDetails($branchDetails);

/*$spreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

foreach ($branches as $branchNum => $data) {

	$summary[$branchNum]['staffing'] = ['totCount'=>0, 'currCount'=>0, 'newCount'=>0, 'changeCount'=>0, 'termCount'=>0,'tenureSum'=>0];
	$summary[$branchNum]['audits'] = ['count'=>0, 'sumScore'=>0];

	foreach ($data as $key => $info) {
		$summary[$branchNum]['name'] = $info['bName'];
		$bName    = $info['bName'];
		$tabName  = $branchNum . " - " . $bName;
		$newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, $tabName);
		$spreadSheet->addSheet($newSheet);
		$spreadSheet->setActiveSheetIndexByName($tabName);
		$sheet         = $spreadSheet->getActiveSheet();
		#writes staffing headers
		$col           = 1;
		$row           = 2;
		$sheet->setCellValueByColumnAndRow(1, 1, $tabName);
		foreach ($tenureHeaders as $title) {
			$sheet->setCellValueByColumnAndRow($col, $row, $title);
			$col ++;
		}
		#writes audit headers
		$sheet->setCellValueByColumnAndRow(9, 2, 'Corp Audits');
		$col = 10;
		$row = 2;
		foreach ($periods as $period) {
			$sheet->setCellValueByColumnAndRow($col, $row, $period);
			$col ++;
		}
		$col = 9;
		$row = 3;
		foreach (array_keys($auditArr) as $auditName) {
			$sheet->setCellValueByColumnAndRow($col, $row, $auditName);
			$row ++;
		}
		#writes staffing details
		$row = 3;
		foreach ($turnOver[$branchNum] as $position => $x) {
			$sheet->setCellValueByColumnAndRow(1, $row, $position);
			$sheet->setCellValueByColumnAndRow(2, $row, $x['count']);
			$sheet->setCellValueByColumnAndRow(3, $row, $x['current']);
			$sheet->setCellValueByColumnAndRow(4, $row, $x['new']);
			$sheet->setCellValueByColumnAndRow(5, $row, $x['changed']);
			$sheet->setCellValueByColumnAndRow(6, $row, $x['termed']);
			$sheet->setCellValueByColumnAndRow(7, $row, $x['averageTenure']);
			$row ++;
			$summary[$branchNum]['staffing']['totCount'] += $x['count'];
			$summary[$branchNum]['staffing']['currCount'] += $x['current'];
			$summary[$branchNum]['staffing']['newCount'] += $x['new'];
			$summary[$branchNum]['staffing']['changeCount'] += $x['changed'];
			$summary[$branchNum]['staffing']['termCount'] += $x['termed'];
			$summary[$branchNum]['staffing']['tenureSum'] += $x['averageTenure'];
		}
		#writes audit details
		$col = 10;
		$row = 3;
		foreach ($writeAudits[$branchNum] as $period => $scores) {
			$totScore = number_format((float)$scores['total'] * 100, 2);
			if ($totScore > 0) {
				$summary[$branchNum]['audits']['count']++;
				$summary[$branchNum]['audits']['sumScore'] += $totScore;
			}
			$sheet->setCellValueByColumnAndRow($col, 3, $totScore);
			$sheet->setCellValueByColumnAndRow($col, 4, number_format((float)$scores['fresh'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 5, number_format((float)$scores['admin'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 6, number_format((float)$scores['cashroom'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 7, number_format((float)$scores['deli'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 8, number_format((float)$scores['floor'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 9, number_format((float)$scores['frontEnd'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 10, number_format((float)$scores['genOps'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 11, number_format((float)$scores['ic'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 12, number_format((float)$scores['meat'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 13, number_format((float)$scores['pest'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 14, number_format((float)$scores['produce'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 15, number_format((float)$scores['receiving'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 16, number_format((float)$scores['reception'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 17, number_format((float)$scores['safety'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 18, number_format((float)$scores['seafood'] * 100, 2));
			$sheet->setCellValueByColumnAndRow($col, 19, number_format((float)$scores['smware'] * 100, 2));
			$col ++;
		}
		#writes inventory headers
		$col = 9;
		$row = 22;
		foreach ($invHeaders as $invHead) {
			$sheet->setCellValueByColumnAndRow($col, $row, $invHead);
			$col++;
		}
		#writes inventory details
		$col = 9;
		$row = 23;
		foreach ($inventories[$branchNum] as $key => $details) {
			$shrinkPer = number_format((float)$details['shrinkPer'] * 100, 2);
			$damPer = number_format((float)$details['damPer']*100, 2);
			$sheet->setCellValueByColumnAndRow(9, $row, $details['invDate']);
			$sheet->setCellValueByColumnAndRow(10, $row, $details['invCount']);
			$sheet->setCellValueByColumnAndRow(11, $row, $details['invAdj']);
			$sheet->setCellValueByColumnAndRow(12, $row, $details['perAdj']);
			$sheet->setCellValueByColumnAndRow(13, $row, $details['netShrink']);
			$sheet->setCellValueByColumnAndRow(14, $row, $shrinkPer);
			$sheet->setCellValueByColumnAndRow(15, $row, $details['netDam']);
			$sheet->setCellValueByColumnAndRow(16, $row, $damPer);
			$row++;
			$summary[$branchNum]['inventory'] = [$details['invDate'], $details['invCount'], $details['invAdj'], $details['netShrink'], $shrinkPer,$details['netDam'], $damPer];
		}
	}
}

$newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, 'Summary');
$spreadSheet->addSheet($newSheet, 0);
$spreadSheet->setActiveSheetIndexByName('Summary');
$sheet         = $spreadSheet->getActiveSheet();

#writes summary page
$row = 1;
$col = 1;
foreach ($sumHeaders as $sums) {

	$sheet->setCellValueByColumnAndRow($col, $row, $sums);
	$col++;
}
$col = 1;
$row++;

foreach ($summary as $branchNum => $groups) {
	$col = 1;
	$sheet->setCellValueByColumnAndRow($col, $row, $branchNum);
	$col++;
	$sheet->setCellValueByColumnAndRow($col, $row, $groups['name']);
	$col++;
	foreach ($groups['staffing'] as $key => $inf) {
		if ($key !== 'tenureSum') {
			$sheet->setCellValueByColumnAndRow($col, $row, $inf);
			$col ++;
		} else {
			$count = $summary[$branchNum]['staffing']['totCount'];
			$avgTenure = number_format(($inf/$count), 2);
			$sheet->setCellValueByColumnAndRow($col, $row, $avgTenure);
			$col++;
		}
	}
	$avgAudit = number_format(($groups['audits']['sumScore']/$groups['audits']['count']), 2);
	$sheet->setCellValueByColumnAndRow($col, $row, $avgAudit);
	$col++;
	foreach ($groups['inventory'] as $key => $x) {
		$sheet->setCellValueByColumnAndRow($col, $row, $x);
		$col++;
	}
	$row++;
}


var_dump($summary);

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadSheet);
$writer->save("../io/output/targetInfo/2020 Target Branch Analysis.xlsx");*/

/*	foreach ($branchDetails as $x => $y) {
echo $x . " - " . count($y) . "</br>";
}*/

var_dump($branchDetails);