<?php
    /**
     * Created by PhpStorm.
     * User: Scott
     * Date: 6/17/2018
     * Time: 10:00 PM
     */

    use PhpOffice\PhpSpreadsheet\Reader\Exception;

    require '../vendor/autoload.php';
    require '../class/Process.php';

    function getAuditArray() {
        $lnk = new Process();
        $sql = "SELECT auditCode, auditName FROM auditlookup";
        $arr = $lnk -> query($sql);
        foreach ($arr as $audit) {
            $audits[$audit['auditName']] = $audit['auditCode'];
        }
        unset($lnk);

        return $audits;
    }

    function getAuditNum($file, $version) {

        $lnk = new Process();

        $auditInfo = explode(" ", $file);

        $year = $auditInfo[0];
        $period = $auditInfo[1];
        $branch = $auditInfo[2];

        $sql = "INSERT INTO enteredaudits (year, period, branch, version) VALUES (?, ?, ?, ?)";
        $params = [$year, $period, $branch, $version];

        $lnk -> query($sql, $params);

        $num = $lnk -> getLastID();

        unset($lnk);

        return $num;

    }

    function readFileData($fileName) {
        $fileType = "Xls";
        $sheetName = "OPS AUDIT RECAP";

        try {
            $reader = $reader = \PhpOffice\PhpSpreadsheet\IOFactory ::createReader($fileType);;
        } catch (Exception $e) {
        }

        $reader -> setReadDataOnly(true);

        try {
            $spreadSheet = $reader -> load($fileName);
        } catch (Exception $e) {
        }

        try {
            $spreadSheet -> setActiveSheetIndexByName($sheetName);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        }

        return $spreadSheet;
    }

    /**
     * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
     * @return int
     */
    function getVersion($spreadSheet) {

        try {
            $range = $spreadSheet -> getActiveSheet() -> rangeToArray("H530:H550");
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        }

        #var_dump($range);

        switch ("TOTAL SCORE") {
            case $range[4][0]:
                $version = 1;
                break;
            case $range[10][0]:
                $version = 2;
                break;
            default:
                $version = "unknown";
                break;
        }

        return $version;

    }

    /**
     * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
     * @return array
     */
    function getFindings($spreadSheet, $version) {

        $audits = getAuditArray();
        $findings = array();

        switch ($version) {
            case 2:
                $range = "C2:K538";
                break;
            case 1:
                $range = "C2:K532";
                break;
            default:
                $range = null;
        }

        if ($range !== null) {
            try {
                $responses = $spreadSheet -> getActiveSheet() -> rangeToArray($range, null, true, false, true);
            } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            }
        }

        foreach ($responses as $key => $data) {
            $qNum = $data["F"];
            if ($qNum != null) {
                $qAudit = $data["I"];
                $qComm = $data["K"];
                $response = $data["C"];
                $code = $audits[$qAudit] . (string)$qNum . "." . $version;
                if ($response !== false) {
                    $findings[$code] = $qComm;
                } elseif ($audits[$qAudit] == "FL") {
                    $varArray = [1, 2, 3, 4, 5, 8];
                    if (in_array($qNum, $varArray) == true && strlen($qComm) > 1) {
                        $findings[$code] = trim($qComm);
                    }
                }
            }
        }

        return $findings;
    }

    /**
     * @param $findings array
     * @param $auditID  int
     * @return string
     */
    function setFindings($findings, $auditID) {

        $lnk = new Process();
        $count = 1;

        foreach ($findings as $qCode => $qComm) {
            $sql = "INSERT INTO auditfindings (auditID, qCode, qComm) VALUES (?, ?, ?)";
            $params = [$auditID, $qCode, $qComm];
            $insert = $lnk -> query($sql, $params);

            #echo $count." ";
            $count++;
        }
        unset($lnk);

        return "</br>Finished with findings</br></br>";
    }

    /**
     * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
     * @param $version     int
     * @return array
     */
    function getScores($spreadSheet, $version) {

        $array = array();

        switch ($version) {
            case 1:
                $totScoreLoc = "L535";
                $freshScoreLoc = "L539";
                $deptScoreLoc = "P543:P560";
                $totCatsLoc = "AF560:AX560";
                break;
            case 2:
                $totScoreLoc = "L541";
                $freshScoreLoc = "L545";
                $deptScoreLoc = "P549:P566";
                $totCatsLoc = "AF566:AX566";
                break;
            default:
                $totScoreLoc = null;
                $freshScoreLoc = null;
                $deptScoreLoc = null;
                $totCatsLoc = null;
                break;
        }

        if ($totScoreLoc !== null) {
            try {
                $totScore = $spreadSheet -> getActiveSheet() -> getCell($totScoreLoc) -> getCalculatedValue();
            } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            }
        }
        if ($freshScoreLoc !== null) {
            try {
                $freshScore = $spreadSheet -> getActiveSheet() -> getCell($freshScoreLoc) -> getCalculatedValue();
            } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            }
        }
        if ($deptScoreLoc !== null) {
            try {
                $deptScores = $spreadSheet -> getActiveSheet() -> rangeToArray($deptScoreLoc, null, true, true, true);
            } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            }
        }
        if ($totCatsLoc !== null) {
            try {
                $totCatsScore = $spreadSheet -> getActiveSheet() -> rangeToArray($totCatsLoc, null, true, true, true);
            } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            }
        }

        $br = "</br>";

        $array[] = $totScore;
        $array[] = $freshScore;

        foreach ($deptScores as $row => $columns) {
            foreach ($columns as $column => $value) {
                if (is_numeric($value)) {
                    $array[] = $value;
                }
            }
        }

        foreach ($totCatsScore as $row => $cells) {
            foreach ($cells as $number => $value) {
                if (!is_null($value)) {
                    $array[] = $value;
                }
            }
        }

        return $array;
    }

    /**
     * @param $scores array
     * @return bool
     */
    function setScores($scores) {

        $lnk = new Process();

        $sql = "INSERT INTO auditscores (totScore, freshScore, adScore, crScore, daScore, flScore, feScore, goScore, 
                                    icScore, meScore, pcScore, prScore, rvScore, rpScore, saScore, seScore, swScore, 
                                    lqScore, fsScore, totFreshScore, totFSafeScore, totOpsScore, totSafeScore, auditID) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert = $lnk -> query($sql, $scores);

        unset($lnk);

        return $insert;
    }

    /**
     * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
     * @param $version     int
     * @return array
     */
    function getPeople($spreadSheet, $version) {

        $array = array();
        $range = array();
        $people = array();

        switch ($version) {
            case 1:
                $range = ["V537:V540", "AG534:AG540", "AP534:AP540", "AY534:AY535"];
                break;
            case 2:
                $range = ["V543:V546", "AG540:AG546", "AP540:AP546", "AY540:AY541"];
                break;
            default:
                $range = null;
                break;
        }

        if ($range !== null) {
            foreach ($range as $rng) {
                try {
                    $people[] = $spreadSheet -> getActiveSheet() -> rangeToArray($rng, null, true, true, true);
                } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
                }
            }
        }

        foreach ($people as $column => $data) {
            foreach ($data as $key => $cells) {
                foreach ($cells as $key2 => $value) {
                    $array[] = $value;
                }
            }
        }

        return $array;
    }

    /**
     * @param $people array
     * @return boolean
     */
    function setPeople($people) {

        $sql = 'INSERT INTO auditpeople (auditor, bm, abm1, abm2, ad, cr, da, fl, fe, go, ic, me, pc, pr, rv, rp, sa, se, sw, 
                                    lq, auditID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $lnk = new Process();

        $insert = $lnk -> query($sql, $people);

        return $insert;

    }

    $auditPath = "C:/Users/Scott/Desktop/Audits/";

    #$file = "2018 Q1 449 Pittsburg.xls";
    $file = trim($_GET['fileName']);
    $fileName = $auditPath . $file;

    $totalStart = microtime(true);

    $readStart = microtime(true);
    $spreadSheet = readFileData($fileName);
    $readEnd = microtime(true);
    echo "</br>Read File in " . ($readEnd - $readStart) . " seconds.</br>";

    $versionStart = microtime(true);
    $version = getVersion($spreadSheet);
    $versionEnd = microtime(true);
    echo "</br>Got version in " . ($versionEnd - $versionStart) . " seconds.</br>";

    $auditNumStart = microtime(true);
    $auditNum = getAuditNum($file, $version);
    $auditNumEnd = microtime(true);
    echo "</br>Got Audit Number in " . ($auditNumEnd - $auditNumStart) . " seconds.</br>";

    $findingsStart = microtime(true);
    $findings = getFindings($spreadSheet, $version);
    $findingsEnd = microtime(true);
    echo "</br>Got Findings in " . ($findingsEnd - $findingsStart) . " seconds.</br>";

    $findingsStart = microtime(true);
    $findingsComp = setFindings($findings, $auditNum);
    $findingsEnd = microtime(true);
    echo "</br>Wrote Findings in " . ($findingsEnd - $findingsStart) . " seconds.</br>";

    $getScoreStart = microtime(true);
    $scores = getScores($spreadSheet, $version);
    $scores[] = $auditNum;
    $getScoreEnd = microtime(true);
    echo "</br>Got Scores in " . ($getScoreEnd - $getScoreStart) . " seconds.</br>";

    $setScoreStart = microtime(true);
    $scoresWritten = setScores($scores);
    $setScoreEnd = microtime(true);
    echo "</br>Wrote Scores in " . ($setScoreEnd - $setScoreStart) . " seconds.</br>";

    $getPeopleStart = microtime(true);
    $people = getPeople($spreadSheet, $version);
    $people[] = $auditNum;
    $getPeopleEnd = microtime(true);
    echo "</br>Got People in " . ($getPeopleEnd - $getPeopleStart) . " seconds.</br>";

    $setPeopleStart = microtime(true);
    #var_dump($people);
    $setPeople = setPeople($people);
    $setPeopleEnd = microtime(true);
    echo "</br>set People in " . ($setPeopleEnd - $setPeopleStart) . " seconds.</br>";

    $totalEnd = microtime(true);
    echo "</br>" . $file . " Parsed in " . ($totalEnd - $totalStart) . " seconds.</br>";

?>