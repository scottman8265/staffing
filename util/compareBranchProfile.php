<?php

require_once '../class/Process.php';

session_start();

if (isset($_SESSION['brProfUpdate'])) {unset($_SESSION['brProfUpdate']);}

$profiles = isset($_POST['profiles']) ? $_POST['profiles'] : null;
$bNum     = isset($_POST['bNum']) ? $_POST['bNum'] : null;
$lnk      = new Process();
$retArr   = ['add' => [], 'sub' => [], 'errors' => []];

function getBranchStaffingProfile($lnk, $bNum) {
	/*$sql = "SELECT position, tmName, posStatus, id FROM staffing.branchStaffing WHERE branchNum = " . $bNum;
	#echo $sql;
	$qry = $lnk->query($sql);
	$z   = [];
	foreach ($qry as $x) {
		$z[$x['position']][] = ['tmName' => $x['tmName'], 'posStatus' => $x['posStatus'], 'tblID' => $x['id']];
	}

	#var_dump($z);*/

	$qry = $lnk->query("SELECT posProfile from branchInfo.branches WHERE branchNum = " . $bNum);

	return unserialize($qry[0]['posProfile']);
}

function setNewProfileArr($profiles) {
	$z = [];
	array_multisort(array_column($profiles, 'viewOrder'), SORT_ASC, $profiles);
	foreach ($profiles as $x) {
		$z[$x['posID']] = $x['posCount'];
	}

	return $z;
}

/**
 * @param $lnk Process
 *
 * @return array
 */
function getPosNames($lnk) {

	$retArr = [];

	$sql = "SELECT posName, posID FROM staffing.positions ORDER BY viewOrder";
	$qry = $lnk->query($sql);
	foreach ($qry as $x) {
		$retArr[$x['posID']] = $x['posName'];
	}

	return $retArr;
}

function updateBranchProfile($profArray, $bNum, $lnk) {
	$y            = serialize($profArray);
	$updateSql    = "UPDATE branchInfo.branches SET posProfile = ? WHERE branchNum = ?";
	$updateParams = [$y, $bNum];
	$updateQry    = $lnk->query($updateSql, $updateParams);
	if ($updateQry) {
		echo "successfully updated";
	}
	else {
		echo "not updated";
	}
}

function compareProfiles($newArr, $currArr, $bNum, $posArr) {

	$changes = ['add' => [], 'sub' => [], 'errors' => []];

	foreach ($newArr as $posID => $newCount) {

		$currCount = isset($currArr[$posID]) ? $currArr[$posID] : 0;

		$diff = $newCount - $currCount;

		#echo 'posID: ' . $posID . " [diff: " . $diff . "]</br>";

		switch (true) {
			case $diff > 0:
				$changes['add'][] = ['posID'   => $posID,
				                     'count'   => $diff];
				break;
			case $diff < 0:
				$changes['sub'][] = ['posID' => $posID,
				                     'count' => ($diff * (- 1))];
				break;
			case !is_numeric($diff) :
				$changes['errors'][] = ['posID' => $posID,
				                        'error' => "something went wrong with posID: " . $posID . " for branch " . $bNum];
				break;
		}
	}

	#var_dump($changes);

	return $changes;
}

/**
 * @param $removePosArr array
 * @param $currProfArr  array
 * @param $retArr       array
 * @param $posArr       array
 *
 * @return mixed
 */
function verifyRemoves($removePosArr, $currProfArr, $retArr, $posArr) {

	$removes    = ['can' => [], 'cant' => []];
	$reqCount   = 0;
	#var_dump($removePosArr);

	foreach ($removePosArr as $key => $x) {
		$reqCount += $x['count'];
		$posCount = $x['count'];
		$posID = $x['posID'];
		$canRemove  = 0;
		$cantRemove = 0;
		foreach ($currProfArr[$posID] as $y) {
			$totCount = count($currProfArr[$posID]);
			$name   = $y['tmName'];
			$status = $y['posStatus'];
			$tblID  = $y['tblID'];
			if (strlen($name) < 4 && $status !== 'Open') {
				$removes['can']['pos'][$posID]['tblID'][] = $tblID;
				$removes['can']['pos'][$posID]['posName'] = $posArr[$posID];
				$removes['can']['pos'][$posID]['totCount'] = $totCount;
				$removes['can']['tblIDs'][] = $tblID;
				$canRemove ++;
			}
			else {
				$removes['cant']['pos'][$posID]['posName'] = $posArr[$posID];
				$cantRemove ++;
			}
		}

		#echo "[canRemove: " . $canRemove . "][posCount: " . $posCount . "]</br>";

		if ($canRemove === $posCount) {
			unset($removes['cant']['pos'][$posID]);
		}
	}

	if (!empty($removes['can'])) {
		foreach ($removes['can']['pos'] as $posID => $y) {
			$removes['can']['pos'][$posID]['totCount'] = $removes['can']['pos'][$posID]['totCount'] - count($removes['can']['pos'][$posID]['tblID']);
		}
	}


	$removes['can']['message']  = "Can Remove these positions";
	$removes['cant']['message'] = "Cannot Remove all of these positions";

	#var_dump($removes);

	$retArr['sub'] = $removes;

	return $retArr;
}



#gets posName Array for messages
$posArr = getPosNames($lnk);
#gets current branch position profile
$currProfArr = getBranchStaffingProfile($lnk, $bNum);
#sets newProfile array from post
$newProfArr = setNewProfileArr($profiles);
#compares newProfile to currentProfile
$compArr = compareProfiles($newProfArr, $currProfArr, $bNum, $posArr);


/*if (!empty($compArr['add'])) {
	#echo "adding " . count($compArr['add']) . " positions</br>";
	$retArr['add'] = $compArr['add'];
}

if (!empty($compArr['sub'])) {
	#echo "removing " . count($compArr['sub']) . " positions</br>";
	$retArr['sub'] = verifyRemoves($compArr['sub'], $currProfArr, $retArr, $posArr);
}

if (!empty($compArr['errors'])) {
	#echo "errors for " . count($compArr['errors']) . " positions</br>";
	$retArr['errors'] = $compArr['errors'];
}*/

$_SESSION['brProfUpdate']['profile'] = $newProfArr;
$_SESSION['brProfUpdate']['bNum'] = $bNum;
#var_dump($retArr);
?>

<div class="verifyProfCont">

    <div class="branchProfChangeContHead">
        <div class="branchProfChangeHeaderText"><h1>Verify Profile Changes</h1></div>
    </div>
	<div class="branchProfChangeFlowWrap">
		<div class="branchProfChangeWrap" data-branchnum="<?php echo $bNum; ?>">
			<div class="branchProfAddsCont branchProfChangeCont">
				<div class="branchProfAddsHead branchProfChangeHeader">
					<p class="branchProfChangeHeadText">The Following Positions will be Added</p>
				</div>
				<div class="branchProfAddsWrap branchProfChangeWrap">
					<?php if (!empty($compArr['add'])) {
						foreach ($compArr['add'] as $key => $addInfo) { ?>
							<div class="branchProfAddsRow branchProfChangeRow">
								<p class="branchProfAddsText branchProfChangeText"><?php echo $posArr[$addInfo['posID']] ?> (<?php echo $addInfo['count'] ?>)</p>
							</div>
						<?php }
					}
					else { ?>
						<div class="branchProfAddsRow branchProfChangeRow">
							<p class="branchProfAddsText branchProfChangeText">There Is Nothing Set To Add</p>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="branchProfSubCont branchProfChangeCont">
				<div class="branchProfSubCanCont branchProfChangeCont">
					<div class="branchProfSubCanHead branchProfChangeHeader">
						<p class="branchProfChangeHeadText">These Positions Will Be Removed </p>
					</div>
					<div class="branchProfSubCanWrap branchProfChangeWrap">
						<?php if (!empty($compArr['sub'])) {
							foreach ($compArr['sub'] as $key => $subInfo) {
								$count    = $subInfo['count'];
								$posName  = $posArr[$subInfo['posID']];
								?>
								<div class="branchProfSubCanRow branchProfChangeRow">
									<p class="branchProfSubCanText branchProfChangeText">
										<?php echo $posName . " (" . $count ?>)</p>
								</div>
							<?php }
						}
						else { ?>
							<div class="branchProfSubCanRow branchProfChangeRow">
								<p class="branchProfSubCanText branchProfChangeText">There is Nothing That Can Be
									Removed</p>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
    <div class="branchVerifyProfBtnRow" data-bnum="<?php echo $bNum; ?>">
        <button class="ui-button ui-corner-all profVerifyBtn" data-type="submit">Submit</button>
        <button class="ui-button ui-corner-all profVerifyBtn" data-type="edit">Edit</button>
    </div>
</div>