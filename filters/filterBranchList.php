<?php

session_start();

require_once '../class/Process.php';

$lnk = new Process();

$execBranchStr = $_SESSION['execData']['branchString'];
$otherBranches = [];
$execBranches = [];

$execBranchSql = 'SELECT branchNum, branchName FROM branchInfo.branches WHERE branchNum IN ('.$execBranchStr.')';
$execBranchQry = $lnk->query($execBranchSql);

foreach ($execBranchQry as $y) {
	$execBranches[$y['branchNum']] = $y['branchName'];
}

$otherBranchSql = "SELECT branchNum, branchName FROM branchInfo.branches WHERE branchNum NOT IN (" . $execBranchStr . ") && location != 'WC'";
$otherBranchQry = $lnk->query($otherBranchSql);

foreach ($otherBranchQry as $x) {
	$otherBranches[$x['branchNum']] = $x['branchName'];
}

ksort($otherBranches);
ksort($execBranches);

$html = '<div class="brFilterInputWrap">
<input type="text" class="brFilterInput" placeholder="Select Branches Below" readonly/>
<button data-type="clear" class="ui-corner-all ui-button brFilterBtn">Clear</button>
<button data-type="submit" class="ui-corner-all ui-button brFilterBtn">Submit</button>
</div><div class="brFilterCont">
<div class="execBrFilterWrapCont brFilterWrapCont">
	<div class="execBrFilterHeader brFilterHeader">
		<span>Yours</span><button data-type="exec" class="ui-button ui-corner-all selectAll execSelect">(un)Select All</button>
	</div>
	<div class="execBrFilterWrap brFilterWrap">
		<div class="execBrFilterData brFilterData">
			<ul class="execBrFilterList brFilterList">';
foreach ($execBranches as $execBrNum => $execBrName) {
	$html .= '<li class="execBrFilterRow brFilterRow" data-bnum="'.$execBrNum.'">'.$execBrNum.' - '.$execBrName.'</li>';
}
$html .= '</ul>
</div>
</div>
</div>
<div class="otherBrFilterWrapCont brFilterWrapCont">
<div class="otherBrFilterHeader brFilterHeader">
<span>Others</span><button data-type="other" class="ui-button ui-corner-all selectAll otherSelect">(un)Select All</button>
</div>
<div class="otherBrFilterWrap brFilterWrap">
<div class="otherBrFilterData brFilterData">
<ul class="otherBrFilterList brFilterList">';
foreach ($otherBranches as $otherBrNum => $otherBrName) {
	$html .= '<li class="otherBrFilterRow brFilterRow" data-bnum="'.$otherBrNum.'">'.$otherBrNum.' - '.$otherBrName.'</li>';
}
$html .= '</ul>
</div>
</div>
</div>
</div>';

echo json_encode(['html'=>$html]);
