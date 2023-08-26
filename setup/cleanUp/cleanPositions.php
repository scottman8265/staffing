<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 10/29/2018
 * Time: 7:06 PM
 */

require ('../../class/Process.php');

$lnk = new Process();

$sql = "SELECT rawPOS FROM staffing.inirawdata";

$qry = $lnk->query($sql);

$uniquePOS = [];

foreach ($qry as $key => $item) {
    if (!isset($item['rawPOS'])) {
        $uniquePOS[$item['rawPOS']] = 1;
    } else {
        $uniquePOS[$item['rawPOS']]++;
    }
}

foreach ($uniquePOS as $position => $count) {
    echo $position .": " . $count . "</br>";
}