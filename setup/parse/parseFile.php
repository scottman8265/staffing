<?php
/**
 * Created by PhpStorm.
 * User: Scott
 * Date: 6/17/2018
 * Time: 10:00 PM
 */

use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

require '../../vendor/autoload.php';
require '../../class/Process.php';

function readFileData($fileName, $sheet) {
    $fileType = "Xls";

    try {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType);;
    } catch (Exception $e) {
    }

    $reader->setReadDataOnly(true);
    $reader->setLoadSheetsOnly($sheet);

    try {
        $spreadSheet = $reader->load($fileName);
    } catch (Exception $e) {
    }

    return $spreadSheet;
}

function getBranchNumbers() {
    $lnk = new Process();

    $data = [];

    $sql = "SELECT branchNum FROM branchinfo.branches WHERE active = true";

    $qry = $lnk->query($sql);

    foreach ($qry as $item => $value) {
        $data[] = $value["branchNum"];
    }

    return $data;

}

/**
 * @param $fileName
 * @return array
 */
function getWorksheetNames($fileName) {

    $inputFileType = 'Xls';
    $data = [];

    try {
        $reader = IOFactory::createReader($inputFileType);
    } catch (Exception $e) {
    }
    $worksheetNames = $reader->listWorksheetNames($fileName);

    foreach ($worksheetNames as $worksheetName) {
        $data[] = $worksheetName;
    }

    return $data;
}

function getBranchSheets($worksheetNames, $branches) {

    $data = [];

    foreach ($branches as $key => $value) {

        $match = preg_grep('/' . $value . '/', $worksheetNames);

        if ($match) {
            $data[] = $match;
        }
    }

    return $data;

}

/**
 * @param $fileName
 * @param $sheet
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 */
function processRawData($fileName, $sheet, $branch, $branchSheet) {

    $rawData = readFileData($fileName, $branchSheet[$sheet]);
    $data = [];

    for ($i = 4; $i < 60; $i++) {
        $col[1] = $rawData->getActiveSheet()->getCell("A" . $i)->getCalculatedValue();
        $col[2] = $rawData->getActiveSheet()->getCell("B" . $i)->getCalculatedValue();
        $col[3] = $rawData->getActiveSheet()->getCell("C" . $i)->getCalculatedValue();
        $col[4] = $rawData->getActiveSheet()->getCell("D" . $i)->getCalculatedValue();
        $col[5] = $rawData->getActiveSheet()->getCell("E" . $i)->getCalculatedValue();
        $col[6] = $rawData->getActiveSheet()->getCell("F" . $i)->getCalculatedValue();
        $col[7] = $rawData->getActiveSheet()->getCell("G" . $i)->getCalculatedValue();
        $col[8] = $rawData->getActiveSheet()->getCell("H" . $i)->getCalculatedValue();

        $verified = verifyLine($col[2], $col[7]);
        #var_dump($col_7);

        if ($verified) {

/*            foreach ($col as $key => $value) {

                if ($key == 1 || $key == 7 || $key == 8 ) {
                    $newValue = strlen($value) < 1 ? "NULL" : $value;
                } elseif ($key == 2) {
                    $newValue = $value;
                } elseif ($key == 3 || $key == 4 || $key == 5 || $key == 6) {
                    $newValue = $value == 'x' || $value == 'X' ? TRUE : FALSE;

                } else {
                    $newValue = "NULL";
                }

                $col[$key] = $newValue;
            }*/

            $data[$branch[$sheet]][] = ['fileID' => 1, 'rawBranch' => $branch[$sheet], 'rawPos' => $col[1], 'rawName' => $col[2],
                'rawHaccp' => $col[3], 'rawHGuard' => $col[4], 'rawSeaHaccp' => $col[5], 'rawSafeServ' => $col[6],
                'rawComments' => $col[7], 'rawKHPhone' => $col[8]];
        }
    }

    return $data;
}

function verifyLine($col_2, $col_7) {

    $valid = true;

    if (is_null($col_2) || preg_match('/(na)(n\/a)(n\\a)/', $col_2)) {
        if (is_null($col_7) || $col_7 == " ") {
            $valid = false;
        }
    }

    return $valid;

}

$fileName = "C:\\xampp\\htdocs\\staffing\\setup\\filesToParse\\2018MWstaffingInitial.xls";

$worksheetNames = getWorksheetNames($fileName);

$branch = getBranchNumbers();

$branchSheets = getBranchSheets($worksheetNames, $branch);

$columns = [];

#$sheet = 11;

foreach ($branchSheets as $key => $value) {

    #echo $key . "</br>";

    try {
        $processedData[] = processRawData($fileName, $key, $branch, $branchSheets);
    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
    }
    #echo count($processedData[$key]) . " lines in array for Branch #: " . $branch[$key] . "</br></br>";

    /*foreach ($processedData as $line) {

        echo $line;

    }*/
}

$lnk = new Process();

foreach ($processedData as $key => $branchInfo) {
    foreach ($branchInfo as $branch => $info) {
        echo $branch . "</br>";
        foreach ($info as $key => $data) {
            $columns = [];
            $values = [];
            foreach ($data as $colHead => $value) {
                #echo $colHead .": " . $value . "</br> ";
                $columns[] = $colHead;
                $values[] = $value;
            }
            $columnHeaders = implode(", ", $columns);
            $insertValues = implode("', '", $values);

            $sql = "INSERT INTO staffing.inirawdata (".$columnHeaders.") VALUES ('".$insertValues."')";

            echo $sql ."</br>";

            $qry = $lnk->query($sql);
        }
        echo "</br>";
    }
}



    #$sql = "INSERT INTO staffing.inirawdata ('".implode(', ', $columns)."')"




#var_dump($processedData);
?>