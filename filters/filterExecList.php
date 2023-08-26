<?php

session_start();

require_once("../class/Process.php");

$lnk      = new Process();
$execList = ["VP" => [], "COO" => [], "Director" => [], "Regional" => [], "Other" => [], "Developer" => []];


$sql = "SELECT regionID, concat(fName, ' ', lName) as execName, position FROM branchInfo.opsExecs WHERE active = 1";
$qry = $lnk->query($sql);

foreach ($qry as $k => $v) {
	$id       = $v['regionID'];
	$name     = $v['execName'];
	$position = $v['position'];

	switch ($position) {
		case 'reg':
			$listPos = 'Regional';
			break;
		case 'dir':
			$listPos = 'Director';
			break;
		case 'vpEC':
			$listPos = 'VP';
			break;
		case 'dev':
			$listPos = 'Developer';
			break;
		case 'cooEC':
		case 'cooMW':
			$listPos = "COO";
			break;
		default:
			$listPos = 'Other';
			break;
	}

	$execList[$listPos][$id] = [$name, $position];
}
$html = "<div>";
foreach ($execList as $pos => $x) {

	$firstLine = "<div class='accordion'><button class='accordionHeader ui-button ui-corner-all'>" . $pos . "</button><div><ul>";

	$html .= $firstLine;

	foreach ($x as $id => $y) {
		$params = $id . ', "' . $y[1] . '", "' . $y[0] . '"';
		$html   .= "<li class='changeExec' data-execid='".$id."' data-execpos='".$y[1]."'>" . $y[0] . "</li>";
	}
	$html .= "</ul></div></div>";
}
$html .= "</div>";

echo json_encode(['html' => $html]);