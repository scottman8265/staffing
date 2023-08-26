<?php

require_once '../class/Process.php';

$bNum = isset($_POST['bNum']) ? $_POST['bNum'] : 114;

$lnk = new Process();

function getBranchName ($lnk, $bNum) {
    $qry = $lnk->query("SELECT branchName FROM branchInfo.branches WHERE branchNum = " . $bNum);

	return $qry ? $qry[0]['branchName'] : "Invalid Branch Num";

}

/**
 * @param $lnk  Process
 * @param $bNum integer
 *
 * @return array
 */
function getProfile($lnk, $bNum) {
	$profSql = "SELECT posProfile FROM branchInfo.branches WHERE branchNum = " . $bNum;
	$profQry = $lnk->query($profSql);

	return unserialize($profQry[0]['posProfile']);
}

/**
 * @param $lnk   Process
 * @param $bProf array
 *
 * @return array
 */
function getPosDetails($lnk) {

	$posDetSql = "SELECT posName, posID, viewOrder FROM staffing.positions";
	$posDetQry = $lnk->query($posDetSql);

	$posDetails = [];
	foreach ($posDetQry as $y) {
		$posDetails[$y['posID']] = ['posName' => $y['posName'], 'viewOrder' => $y['viewOrder']];
	}

	return $posDetails;
}

/**
 * @param $lnk   Process
 * @param $bProf array
 *
 * @return array
 */
function fillZeroPos($lnk, $bProf) {
	$posArr = array_keys($bProf);
	$posStr = implode(", ", $posArr);

	$zeroPosSql = "SELECT posID FROM staffing.positions WHERE posID NOT IN (" . $posStr . ") ORDER BY viewOrder";
	$zeroPosQry = $lnk->query($zeroPosSql);

	foreach ($zeroPosQry as $x) {
		$bProf[$x['posID']] = 0;
	}

	return $bProf;
}

$bName      = getBranchName($lnk, $bNum);
$bProf      = getProfile($lnk, $bNum);
$posDetails = getPosDetails($lnk);
$bProf      = fillZeroPos($lnk, $bProf);

?>

<div class="branchProfCont">
    <div class="branchProfHead">
        <div class="branchProfHeadBtn">
            <button class="bProfSubmit ui-button ui-corner-all">Submit</button>
        </div>
        <div class="branchProfHeadText"><p>Branch Profile For <?php echo $bNum; ?> - <?php echo $bName; ?></p></div>

    </div>
    <div class="branchProfWrap" data-branchnum="<?php echo $bNum; ?>">
        <div class="branchProfData">
			<?php foreach ($bProf as $posID => $count) { ?>
                <div class="branchProfRow" data-posid="<?php echo $posID ?>"
                     data-vieworder="<?php echo $posDetails[$posID]['viewOrder']; ?>">
                    <div class="posName"><?php echo $posDetails[$posID]['posName']; ?></div>
                    <div class="posInfo">
                        <div class="subtract arrowIcon"></div>
                        <div class="posCount"><?php echo $count; ?></div>
                        <div class="add arrowIcon"></div>
                    </div>
                </div>
			<?php } ?>
        </div>
    </div>
</div>
