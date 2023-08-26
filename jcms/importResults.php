<?php

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '1500');

echo "test";

require_once '../class/Process.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

$file = '../io/input/jcms/Employee Tests 122120 raw.xlsx';
#$file = '../io/input/Combined Manager Rankings 09 2020.xlsx';
$lnk   = new Process();
$count = 0;

$tests = ['Admin Test ',
          'Cashroom Test ',
          'Deli Test ',
          'Floor Test ',
          'Front End Test ',
          'Gen OPS Test ',
          'IC Test',
          'Meat Test ',
          'Produce Test ',
          'Receiving Test ',
          'Reception Test ',
          'Safety Test ',
          'Seafood Test ',
          'Smallwares Test '];

function loadSheet($file, $sheet) {
	try {
		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
		#$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		$reader->setReadDataOnly(true);
		$reader->setLoadSheetsOnly($sheet);
		$spreadSheet = $reader->load($file);

		#$spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
		return $spreadSheet;
	} catch (Exception $e) {
		#die('Error Loading File: ' . $e->getMessage());
		echo "Error";
	}

}

/**
 * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
 * @param $branch
 */
function getData($sheet, $branch) {

	$row      = 8;
	$arr      = [];
	$mrBranch = (int)$branch;

	for ($i = $row; $i < 18; $i ++) {
		$mrPos     = getPosNum($sheet->getCellByColumnAndRow($i, 1)->getValue());
		$mrName    = clean($sheet->getCellByColumnAndRow($i, 3)->getValue());
		$mrDate    = preg_replace('/\\\\/', "/", $sheet->getCellByColumnAndRow($i, 2)->getFormattedValue());
		$mrLdshp   = (int)$sheet->getCellByColumnAndRow($i, 4)->getValue();
		$mrMulti   = (int)$sheet->getCellByColumnAndRow($i, 5)->getValue();
		$mrPrior   = (int)$sheet->getCellByColumnAndRow($i, 6)->getValue();
		$mrMngPeo  = (int)$sheet->getCellByColumnAndRow($i, 7)->getValue();
		$mrPride   = (int)$sheet->getCellByColumnAndRow($i, 8)->getValue();
		$mrCusSer  = (int)$sheet->getCellByColumnAndRow($i, 9)->getValue();
		$mrProc    = (int)$sheet->getCellByColumnAndRow($i, 10)->getValue();
		$mrExec    = (int)$sheet->getCellByColumnAndRow($i, 11)->getValue();
		$mrKnow    = (int)$sheet->getCellByColumnAndRow($i, 12)->getValue();
		$mrCommu   = (int)$sheet->getCellByColumnAndRow($i, 13)->getValue();
		$mrComment = clean($sheet->getCellByColumnAndRow($i, 15)->getValue());

		$test = array_sum([$mrLdshp,
		                   $mrMulti,
		                   $mrPrior,
		                   $mrMngPeo,
		                   $mrPride,
		                   $mrCusSer,
		                   $mrProc,
		                   $mrExec,
		                   $mrKnow,
		                   $mrCommu]);
		#echo $test . "</br>";
		if ($test > 0) {
			$arr[] = [$mrBranch,
			          $mrPos,
			          $mrName,
			          $mrDate,
			          $mrLdshp,
			          $mrMulti,
			          $mrPrior,
			          $mrMngPeo,
			          $mrPride,
			          $mrCusSer,
			          $mrProc,
			          $mrExec,
			          $mrKnow,
			          $mrCommu,
			          $mrComment];
		}
	}

	return $arr;
}

function getPosNum($pos) {
	switch ($pos) {
		case "BM":
			return 136;
		case "ABM":
			return 126;
		case "IC":
			return 151;
		case "PERISHABLE":
			return 191;
		case "DAIRY":
			return 139;
		case "MEAT":
			return 152;
		case "PRODUCE":
			return 156;
		case "SEAFOOD":
			return 163;
		case "FLOOR":
			return 143;
		case "RECEIVING":
			return 159;
		case "HRADMIN":
			return 176;
		case "SMALLWARES":
			return 165;
		case "CASHROOM":
			return 137;
		case "FRONT END":
			return 148;
	}
}

$data      = [];
$insert    = [];
$insertSql = "INSERT INTO staffing.mrRankings (mrBranch, mrPos, mrName, mrDate, mrLdshp, mrMulti, mrPrior, mrMngPeo, mrPride, mrCusSer, mrProc, mrExec, mrKnow, mrCommu, mrComment) VALUES";

foreach ($tests as $test) {
	#$testName    = $test . " Test ";
	$spreadSheet = loadSheet($file, $test);
	$sheet       = $spreadSheet->getActiveSheet();
	$highRow     = $sheet->getHighestRow();
	echo "</br>" . $test . ": " . $highRow . "</br>";
}



