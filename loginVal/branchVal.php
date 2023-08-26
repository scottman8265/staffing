<?php

session_start();

$_SESSION['branchData'] = [];

require_once '../class/Process.php';
require_once '../util/cleanInput.php';
require_once '../class/Arrays.php';

$arrs = new Arrays();

$arrs->setStaffingPosArray();
$arrs->setBranchArray();

$lnk = new Process();

$cleanType = 'required';

$tmID     = isset($_POST['tmID']) ? clean($_POST['tmID']) : 'No ID Received';
$tmName   = isset($_POST['tmName']) ? clean($_POST['tmName']) : null;
$tmLoc    = isset($_POST['tmLoc']) ? clean($_POST['tmLoc']) : null;
$tmBranch = isset($_POST['tmBranch']) ? clean($_POST['tmBranch']) : null;
$tmPos    = isset($_POST['tmPos']) ? clean($_POST['tmPos']) : null;
$from     = isset($_POST['from']) ? $_POST['from'] : null;
$tmNick   = isset($_POST['tmNick']) ? $_POST['tmNick'] : null;

if (strlen($tmLoc) === 2) {
	$threeDigSql = "SELECT branchNum FROM branchInfo.branches WHERE _2DigNum = " . $tmLoc;
	$threeDigQry = $lnk->query($threeDigSql);
	$tmLoc       = $threeDigQry ? $threeDigQry[0]['branchNum'] : "Branch Number Not Found";
}

$findSql = "SELECT tmName, position, branchNum FROM staffing.branchStaffing WHERE empID = " . $tmID;
$findQry = $lnk->query($findSql);

if ($findQry) {
	if ($from === 'login') {
		#echo "</br>inside login if</br>";
		$tmName = $tmName === '-' ? $findQry[0]['tmName'] : 'Team Member Not Found';
		$tmLoc  = $tmLoc === '-' ? $findQry[0]['branchNum'] : 'Location Not Found';
		$tmPos  = $tmPos === '-' ? $arrs->getPosName($findQry[0]['position']) : 'Position Not Found';
	}

	if (isset($tmLoc) && $tmLoc !== 'Location Not Found') {
		$branchSql = "SELECT branchName FROM branchInfo.branches WHERE branchNum = " . $tmLoc;
		$branchQry = $lnk->query($branchSql);
		$tmBranch  = $branchQry ? $arrs->getBranchName($tmLoc) : 'Invalid Branch Number';
	}
} else {
    $tmID = "Error - Try Again";
    $tmName = "Error - Try Again";
    $tmLoc = "Error - Try Again";
    $tmPos = "Error - Try Again";
    $tmBranch = "Error - Try Again";
    $tmNick = "Error - Try Again";
}

$_SESSION['branchData'] = ['tmID'     => $tmID,
                           'tmName'   => $tmName,
                           'tmLoc'    => $tmLoc,
                           'tmPos'    => $tmPos,
                           'tmBranch' => $tmBranch,
                           'tmNick'   => $tmNick];
?>


<div class="branchValContainer">
    <div class="branchValHeader">
        <h4>Verify Log In Information</h4>
    </div>
    <div class="branchValWrapper">
        <div class="branchInfoWrapper tmID">
            <div class="branchValLabel">
                <p>Employee ID: <?php echo $tmID; ?></p>
            </div>
        </div>
        <div class="branchInfoWrapper tmName">
            <div class="branchValLabel">
                <p>Your Name: <?php echo $tmName; ?></p>
            </div>
        </div>
<!--        <div class="branchInfoWrapper tmNick">
            <div class="branchValLabel">
                <p>What Shall I Call You?</p>
            </div>
            <div class="branchValInput">
                <input class="tmNick input2" type="text" name="tmNick" value="<?php /*echo $tmNick; */?>"/>
            </div>
        </div>-->
        <div class="branchInfoWrapper tmLoc">
            <div class="branchValLabel">
                <p>Your Branch Num: <?php echo $tmLoc; ?></p>
            </div>
        </div>
        <div class="branchInfoWrapper tmBranch">
            <div class="branchValLabel">
                <p>Your Branch Name: <?php echo $tmBranch; ?></p>
            </div>
        </div>
        <div class="branchInfoWrapper tmPos">
            <div class="branchValLabel">
                <p>Your Position: <?php echo $tmPos; ?></p>
            </div>
        </div>
        <input class="from" type="hidden" name="from" value="validate"/>
        <div class="branchValOptions">
            <button data-class="submit" data-bNum="<?php echo $tmLoc ?>" class="branchValidation ui-button ui-corner-all">This Is</br>Correct</button>
            <button data-class="reverify" class="branchValidation ui-button ui-corner-all">This Is</br>Not Me</button>
        </div>
    </div>
</div>
