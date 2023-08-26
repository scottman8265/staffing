<?php

require_once '../class/Process.php';
require_once  '../vendor/autoload.php';
require_once  '../util/cleanInput.php';

$lnk = new Process();

$fileName = "../io/input/staffingList.xlsx";

$spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);

use \PhpOffice\PhpSpreadsheet\Exception;

$row = 2;
$col = 3;

$highRow = $spreadSheet->getActiveSheet()->getHighestRow() + 1;

for ($i = $row; $i < $highRow; $i++) {
	$name = $spreadSheet->getActiveSheet()->getCellByColumnAndRow($col, $i);

	$sql = "SELECT empID FROM staffing.branchStaffing WHERE tmName = '". clean($name)."'";
	$qry = $lnk->query($sql);

	if ($qry) {
		$spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col+1, $i, $qry[0]['empID']);
	}
}
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadSheet);
$writer->save("../io/output/staffingList.xlsx");

