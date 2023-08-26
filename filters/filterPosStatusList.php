<?php

session_start();

require_once("../class/Process.php");

$lnk = new Process();

$sql        = "SELECT id, statusName from staffing.posStatus";
$qry        = $lnk->query($sql);
$statusList = [];
$html       = "<ul>";

foreach ($qry as $k => $v) {
	$statusID = $v['id'];
	$statusName = $v['statusName'];
		$html .= "<li class='statusFilter' data-statusid='" . $statusID . "'>" . $statusName . "</li>";

}

$html .= "</ul>";

echo json_encode(['html' => $html]);