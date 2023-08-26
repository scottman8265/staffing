<?php

session_start();

require_once("../class/Process.php");

$lnk = new Process();

$posSql = "SELECT posID, posName, posClass FROM staffing.positions ORDER BY viewOrder";
$posQry = $lnk->query($posSql);

$classSql = "SELECT className, classAbr, classID FROM staffing.posClasses ORDER BY classOrder";
$classQry = $lnk->query($classSql);

$classList = [];
$classKeys = [];
$classIDs  = [];
$positions = [];

foreach ($classQry as $classes) {
	$classList[] = $classes['className'];
	$classKeys[] = $classes['classAbr'];
	$classIDs[]  = $classes['classID'];
}

foreach ($posQry as $posses) {
	$classKey                                        = array_search($posses['posClass'], $classKeys);
	$positions[$classList[$classKey]]['positions'][] = [$posses['posID'], $posses['posName']];
	$positions[$classList[$classKey]]['classID']     = $classIDs[$classKey];
}

$html = "<div id='filterPos' data-type='" . $_SESSION['axsLevel'] . "'>";

foreach ($positions as $className => $x) {

	$firstLine = "<div data-type='class' data-classid='" . $x['classID'] . "' class='posClassBtn class accordion'><button class='accordionHeader ui-button ui-corner-all'>" . $className . "</button><div><ul>";
	$html      .= $firstLine;

	foreach ($x['positions'] as $key => $y) {
		$params = $y[0] . ', "' . $y[1] . '", "' . $className . '"';
		$html   .= "<li data-type='position' data-posid='" . $y[0] . "' class='posBtn position'>" . $y[1] . "</li>";
	}
	$html .= "</ul></div></div>";
}
$html .= "<div data-type='class' data-classid='999' class='posClassBtn class'><button class='ui-button ui-corner-all'>Clear Filter</button></div>";
$html .= "</div>";

echo json_encode(['html' => $html]);