<?php

    require '../class/Process.php';

    function getEnteredAudits() {

        $data = array();

        $lnk = new Process();

        $sql = "SELECT year, period, branch from enteredaudits";

        $query = $lnk -> query($sql);

        foreach ($query as $key => $val) {
            $data[$val['year']][$val['period']][] = $val['branch'];
        }

        return $data;

    }

    $dir = "C:/Users/Scott/Desktop/Audits";

    $data = array();
    $toParse = array();

    $processed = getEnteredAudits();

    $files = scandir($dir);

    $count = 0;

    $html = null;

    foreach ($files as $file) {
        $branchNum = "fuck";
        $parsed = false;
        $year = "what";
        $period = "the";
        $color = "yellow";
        $status = " *** [in queue] ***";
        if (strlen($file) > 2) {

            $auditInfo = explode(" ", $file);
            $auditBranch = $auditInfo[2];
            $auditYear = $auditInfo[0];
            $auditPeriod = $auditInfo[1];

            if (array_key_exists($auditYear, $processed)) {
                $year = $auditYear;
                if (array_key_exists($auditPeriod, $processed[$auditYear])) {
                    $period = $auditPeriod;
                    if (array_search($auditBranch, $processed[$auditYear][$auditPeriod]) !== false) {
                        $branchNum = $auditBranch;
                        $parsed = true;
                        $color = "lightblue";
                        $status = "*** [previously parsed] ***";
                    } else {
                        $branchNum =
                            "[array value][[" . $processed[$auditYear][$auditPeriod][$count] . "]][file value][[" .
                            $auditBranch . "]]</br>";

                        $status = "*** [error parsing] ***";
                    }
                }
            }

            $parsed ?: $toParse[] = $count;

            $x =  "<div id='file" . $count . "' class='line' data-file='" . $count . "' data-parsed='" . $parsed .
                 "' style='position:relative; background-color:" . $color . "; height:25px; width: 100%;'>
                        <div id='fileName" . $count . "' style='float:left;'>" . $file . "</div>
                        <div id='status" . $count . "' style='float:left;'>" . $status . "</div>
                  </div><div id='times".$count."'></div>";

            $html ? $html .= $x : $html = $x;



            $count++;
        }

    }
    if (!empty($toParse)) {
        $data = ['html' => $html, 'toParse' => $toParse];
    } else {
        $data = ['html' => '<div><h1>Scanned '.($count +1).' Files -- All Clean</h1></div>', 'toParse' => $toParse];
    }

    echo json_encode($data);

    #echo $html;