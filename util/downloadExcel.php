<?php
session_start();

require_once '../class/Process.php';
$lnk = new Process();

/**
 * @param $lnk Process
 *
 * @return array
 */
function getRegionals($lnk) {
	$regionalQry = $lnk->query("SELECT concat(fName, ' ', lName) as name, lName, regionID FROM branchInfo.opsExecs where active = 1");

	$arr = [];

	foreach ($regionalQry as $data) {
		$arr[$data['regionID']] = ['fullName' => $data['name'], 'lName' => $data['lName']];
	}

	return $arr;
}

/**
 * @param $lnk Process
 *
 * @return array
 */
function getBranchInfo($lnk) {
	$branchQry = $lnk->query("SELECT branchName, regional, location, branchNum FROM branchInfo.branches");
	$arr       = [];
	foreach ($branchQry as $data) {
		$arr[$data['branchNum']] = ['regional' => $data['regional'],
		                            'name'     => $data['branchName'],
		                            'region'   => $data['location']];
	}

	return $arr;
}

/**
 * @param $lnk Process
 *
 * @return array
 */
function getPositions($lnk) {
	$posQry = $lnk->query("SELECT posID, posName FROM staffing.positions");
	$arr    = [];
	foreach ($posQry as $data) {
		$arr[$data['posID']] = $data['posName'];
	}

	return $arr;
}


$regionals  = getRegionals($lnk);
$branchInfo = getBranchInfo($lnk);
$positions  = getPositions($lnk);

$fileName    = 'testFile.xlsx';
$table       = isset($_SESSION['tables']) ? unserialize($_SESSION['tables']) : null;
$execHeaders = ['Region',
                'Regional',
                'Branch #',
                'Branch Name',
                'Position',
                'Name',
                'Employee ID',
                'Status',
                'Date In Position',
                'Phone Number',
                'Last Updated'];

#var_dump($table);

?>

<table class="downloadedTable">
    <thead>
	<?php foreach ($execHeaders as $head) { ?>
        <th><?php echo $head ?></th>
	<?php } ?>
    </thead>
    <tbody>
	<?php foreach ($table as $branchNum => $tblIDs) {
	$region = isset($branchInfo[$branchNum]['region']) ? $branchInfo[$branchNum]['region'] : "n/a";
	$regionalCode = isset($branchInfo[$branchNum]['regional']) ? $branchInfo[$branchNum]['regional'] : "n/a";
	$branchName = isset($branchInfo[$branchNum]['name']) ? $branchInfo[$branchNum]['name'] : "n/a";
	$regional = isset($regionals[$regionalCode]['lName']) ? $regionals[$regionalCode]['lName'] : "n/a";

	foreach ($tblIDs as $tblID => $cols) { ?>

    <tr>
        <td><?php echo $region ?></td>
        <td><?php echo $regional ?></td>
        <td><?php echo $branchNum ?></td>
        <td><?php echo $branchName ?></td>
        <?php foreach ($cols as $col => $data) {

        switch ($col) {
        case 'Position':
        $insert = $positions[$data['data']];
        break;
        case 'bName':
        $insert = null;
        break;
        default:
        $insert = $data['data'];
        break;
        }

        ?>

        <td><?php if ($insert) {
				echo $insert;
			} ?></td>
	<?php } ?>
    </tr>
	<?php }
	} ?>
    </tbody>
</table>
