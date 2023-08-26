<?php /** @noinspection PhpUndefinedClassInspection */

date_default_timezone_set('America/Chicago');

session_start();

require_once '../class/Process.php';
require_once '../util/cleanInput.php';

$_SESSION['axsLevel'] = 'exec';

$needProcess = false;

$execID       = isset($_POST['execData']) ? $_POST['execID'] : isset($_SESSION['execData']['execID']) ? $_SESSION['execData']['execID'] : null;
$login        = isset($_POST['execData']) ? $_POST['login'] : isset($_SESSION['execData']['login']) ? $_SESSION['execData']['login'] : null;
$pin          = isset($_POST['execData']) ? $_POST['pin'] : isset($_SESSION['execData']['pin']) ? $_SESSION['execData']['pin'] : null;
$execName     = isset($_POST['filters']) ? $_POST['execName'] : isset($_SESSION['filters']['execName']) ? $_SESSION['filters']['execName'] : null;
$firstName    = isset($_POST['execData']) ? $_POST['firstName'] : isset($_SESSION['execData']['firstName']) ? $_SESSION['execData']['firstName'] : null;
$pos          = isset($_POST['execData']) ? $_POST['position'] : isset($_SESSION['execData']['position']) ? $_SESSION['execData']['position'] : null;
$statusSearch = isset($_POST['execData']) ? $_POST['statusSearch'] : isset($_SESSION['execData']['statusSearch']) ? $_SESSION['execData']['statusSearch'] : null;

$execHeaders = ['Branch',
                'Position',
                'Name',
                'Employee ID',
                'Status',
                'Date In Position',
                'Phone Number',
                'Info',
                'Last Updated'];
$execFooters = ['Exec',
                'Branch',
                'Status',
                'Position',
                'Level',
                'Upcoming',
                'Upcoming',
                'Upcoming',
                'Upcoming',
                'Log Out'];

$z          = [];
$info       = [];
$branchInfo = [];
$branchTest = [];

# sets positionName from position from login
switch ($pos) {
	case 'reg':
		$position = 'Regional';
		break;
	case 'dir':
		$position = 'Director';
		break;
	case 'vp':
		$position = "Vice Pres";
		break;
	case 'coo':
		$position = "COO";
		break;
	case 'dev':
		$position = "Developer";
		break;
	default:
		$position = "Other";
		break;
}

function getBranchArray($pos, $execID) {

	$lnk        = new Process();
	$z          = [];
	$bVerifySql = null;

	#getsSql to get staffing from branches based on exec position
	switch ($pos) {
		case 'vp':
		case 'coo':
		case 'dev':
			$bVerifySql = "SELECT branchName, branchNum FROM branchInfo.branches WHERE location != 'WC'";
			break;
		case 'reg':
			$bVerifySql = "SELECT branchName, branchNum FROM branchInfo.branches WHERE regional = " . $execID;
			break;
		case 'dir':
			$bVerifySql = "SELECT branchName, branchNum FROM branchInfo.branches WHERE director = " . $execID;
			break;
	}

	$bVerifyQry = $lnk->query($bVerifySql);

	foreach ($bVerifyQry as $x => $y) {
		$z[$y['branchNum']] = $y['branchName'];
	}

	ksort($z);

	return $z;
}

function getPositions() {
	$lnk = new Process();
	$pos = [];
	$sql = "SELECT posID, posName, essential, viewOrder FROM staffing.positions ORDER BY viewOrder ASC";
	$qry = $lnk->query($sql);

	foreach ($qry as $a => $b) {
		$pos[$b['posID']] = ['posName' => $b['posName'], 'essential' => $b['essential']];
	}

	return $pos;
}

function getInfoArray($branchString, $pos) {
	$lnk       = new Process();
	$lineCount = 1;

	$branchSql = "SELECT id, branchNum, position, empID, tmName, dateInPos, posStatus, updated, phoneNum 
                         FROM staffing.branchStaffing 
                         WHERE branchDisplay = 1 && branchNum IN (" . $branchString . ") && " . $_SESSION['filters']['status'] . " && " . $_SESSION['filters']['position'] . " ORDER BY branchNum";

	$branchQry = $lnk->query($branchSql);
	$info      = [];
	foreach ($branchQry as $k => $v) {
		$bNum = $v['branchNum'];
		#$_SESSION['counts']['positions'] ++;
		$info[$bNum][$v['id']] = ['Position'     => ['data'     => $v['position'],
		                                             'execOnly' => false],
		                          'Name'         => ['data'     => $v['tmName'],
		                                             'execOnly' => false],
		                          'Employee ID'  => ['data'     => $v['empID'],
		                                             'execOnly' => false],
		                          'Status'       => ['data'     => $v['posStatus'],
		                                             'execOnly' => false],
		                          'Since'        => ['data'     => $v['dateInPos'],
		                                             'execOnly' => false],
		                          'Phone Number' => ['data'     => $v['phoneNum'],
		                                             'execOnly' => false],
		                          'Updated'      => ['data'     => $v['updated'],
		                                             'execOnly' => false],
		                          'bName'        => $_SESSION['filters']['branch'][$bNum]];
		$lineCount ++;
	}

	return $info;

}

function getExecOnly($branchInfo) {
	$lnk = new Process();

	$searchSql = "SELECT tblID, field, updateTo from staffing.updateLog WHERE updateType = 'execOnly'";
	$searchQry = $lnk->query($searchSql);

	if ($searchQry) {
		foreach ($searchQry as $k => $v) {
			$execSql    = "SELECT id, branchNum FROM staffing.branchStaffing WHERE id = ?";
			$execParams = [$v['tblID']];
			$execQry    = $lnk->query($execSql, $execParams);
			if ($execQry) {
				foreach ($execQry as $key => $x) {
					$bNum     = $x['branchNum'];
					$id       = $x['id'];
					$field    = $v['field'];
					$updateTo = $v['updateTo'];
					$tblID    = $v['tblID'];

					switch ($field) {
						case 'position':
							$fieldName = 'Position';
							break;
						case 'tmName':
							$fieldName = 'Name';
							break;
						case 'empID':
							$fieldName = 'Employee ID';
							break;
						case 'posStatus':
							$fieldName = 'Status';
							break;
						case 'dateInPos':
							$fieldName = 'Since';
							break;
						case 'updated':
							$fieldName = 'Updated';
							break;
						case 'phoneNum':
							$fieldName = 'Phone Number';
							break;
						default:
							$fieldName = null;
							break;
					}

					if (isset($branchInfo[$bNum])) {
						$branchInfo[$bNum][$id][$fieldName]['data']     = $updateTo;
						$branchInfo[$bNum][$id][$fieldName]['execOnly'] = true;
					}
				}
			}
		}
	}

	#$_SESSION['test']['execOnlyBranchInfo'] = $branchInfo;
	#$_SESSION['test']['searchQry']          = $searchQry;

	#var_dump($branchInfo);

	return $branchInfo;
}

function execOnlyClass() {
	echo ' execOnly';
}

function createTblHeader($header) {
	global $tblHtml;

	switch ($header) {
		case "Branch":
		case "Position":
		case "Status":
			$tblHtml .= "<th class='sort'><div class='filter filterable' data-filter='filter".$header."'></div>" . $header . "</th>";
			break;
		default:
			$tblHtml .= "<th class='sort'>" . $header . "</th>";
			break;
	}

}

function createTblFooter($footer) {
	global $tblHtml;
	$skipItems = ['Upcoming', 'Log Out'];
	$filter    = '"filter' . $footer . '"';
	if (!in_array($footer, $skipItems)) {
		$tblHtml .= "<td id='filter" . $footer . "' onclick='execFilters(" . $filter . ")' class='execOpt menu'>Filter By<br>" . $footer . "</td>";
	}
    elseif ($footer !== 'Log Out') {
		$tblHtml .= "<td class='execOpt menu'>Upcoming Option</td>";
	}
	else {
		$tblHtml .= "<td id='logOut' onclick='logOut()' class='execOpt menu'>Log</br>Out</td>";
	}
}

$branchString   = $_SESSION['filters']['branchString'];
$branchArray    = isset($_SESSION['filters']['branch']) ? $_SESSION['filters']['branch'] : getBranchArray($pos, $execID);
$posArray       = getPositions();
$initBranchInfo = getInfoArray($branchString, $posArray);
$branchInfo     = getExecOnly($initBranchInfo);

$tblHtml = '<div id="tableWrapper"><table class="paleBlueRows" data-updatetype="exec"><thead>';
array_map("createTblHeader", $execHeaders);
$tblHtml .= "</thead>";
/*$tblHtml .= "</thead><tfoot>";
array_map("createTblFooter", $execFooters);
$tblHtml   .= "</tfoot>";*/
$lineCount = 1;
if (!empty($branchInfo)) {
	echo $tblHtml ?>
    <tbody>
	<?php foreach ($branchInfo as $branchNum => $info) {
		foreach ($info as $id => $data) {
			isset($data['Position']['data']) ? $posName = $posArray[$data['Position']['data']]['posName'] : $_SESSION['errors'][$branchNum][] = 'position';
			isset($data['Position']['execOnly']) ? $posNameExec = $data['Position']['execOnly'] : $_SESSION['errors'][$branchNum][] = 'positionExecOnly';
			isset($data['Name']['data']) ? $tmName = $data['Name']['data'] : $_SESSION['errors'][$branchNum][] = 'name';
			isset($data['Name']['execOnly']) ? $tmNameExecOnly = $data['Name']['execOnly'] : $_SESSION['errors'][$branchNum][] = 'nameExecOnly';
			isset($data['Status']['data']) ? $posStatus = $data['Status']['data'] : $_SESSION['errors'][$branchNum][] = 'status';
			isset($data['Status']['execOnly']) ? $posStatusExecOnly = $data['Status']['execOnly'] : $_SESSION['errors'][$branchNum][] = 'statusExecOnly';
			isset($data['Since']['data']) ? $since = $data['Since']['data'] : $_SESSION['errors'][$branchNum][] = 'since';
			isset($data['Since']['execOnly']) ? $sinceExecOnly = $data['Since']['execOnly'] : $_SESSION['errors'][$branchNum][] = 'sinceExecOnly';
			isset($data['Updated']['data']) ? $updated = $data['Updated']['data'] : $_SESSION['errors'][$branchNum][] = 'updated';
			isset($data['Updated']['execOnly']) ? $updatedExecOnly = $data['Updated']['execOnly'] : $_SESSION['errors'][$branchNum][] = 'updatedExecOnly';
			isset($data['Phone Number']['data']) ? $phone = $data['Phone Number']['data'] : $_SESSION['errors'][$branchNum][] = 'phone';
			isset($data['Phone Number']['execOnly']) ? $phoneExecOnly = $data['Phone Number']['execOnly'] : $_SESSION['errors'][$branchNum][] = 'phoneExecOnly';
			isset($data['Employee ID']['data']) ? $empID = $data['Employee ID']['data'] : $_SESSION['errors'][$branchNum][] = 'employeeID';
			isset($data['Employee ID']['execOnly']) ? $empIDExecOnly = $data['Employee ID']['execOnly'] : $_SESSION['errors'][$branchNum][] = 'employeeIDExecOnly';
			isset($data['bName']) ? $bName = $data['bName'] : $_SESSION['errors'][$branchNum][] = 'bName';
			?>

            <tr id= <?php echo $id; ?>>
                <td class="branchInfoCell">
                    <div class="branchProfileIcon icon" data-bnum="<?php echo $branchNum ?>">
                        <img src='images/icons/branchProfile.png'
                             height='32px' width='32px'
                             alt='View Branch Profile'
                             title="Click to View/Edit Branch Profile"/>
                    </div>
                    <div class="branchInfoWrap">
                        <div class="branchNum"><?php echo $branchNum; ?></div>
                        <div class="branchName"><?php echo $bName; ?></div>
                    </div>
                </td>
                <td class='position'><?php echo $posName; ?></td>
                <td data-field="name" class='name changeable update<?php if ($tmNameExecOnly) {
					execOnlyClass();
				} ?>'><?php echo $tmName; ?></td>
                <td data-field="empID" class='empID changeable update<?php if ($empIDExecOnly) {
					execOnlyClass();
				} ?>'><?php echo $empID; ?></td>
                <td data-field="posStatus" class='posStatus changeable update<?php if ($posStatusExecOnly) {
					execOnlyClass();
				} ?>'><?php echo $posStatus; ?></td>
                <td data-field="date" class='date changeable update<?php if ($sinceExecOnly) {
					execOnlyClass();
				} ?>'><?php echo $since; ?></td>
                <td data-field="phoneNum" class='phoneNum changeable update<?php if ($phoneExecOnly) {
					execOnlyClass();
				} ?>'><?php echo $phone; ?></td>
                <td class='iconContainer' data-id='<?php echo $id ?>'>
                    <div class="row iconWrapper">
                        <div class="icon col-md-6 certIcon"><img src='images/icons/certificate2a.png'
                                                                 height='32px' width='32px'
                                                                 alt='View Certificates'
                                                                 title="View Certifications"/></div>
                        <div class="icon col-md-6 commentIcon"><img src='images/icons/comment.png'
                                                                    height='32px' width='32px'
                                                                    alt='View Comments'
                                                                    title="View Comments"/></div>
                    </div>
                    <div class="row iconWrapper">
                        <div class="icon col-md-6 tmProfileIcon"><img src='images/icons/tmProfile.png'
                                                                      height='32px' width='32px'
                                                                      alt='View TM Profile'
                                                                      title="View TM Profile"/></div>
                        <div class="icon col-md-6 keyIcon"><img src='images/icons/key.png'
                                                                height='32px' width='32px'
                                                                alt='Change Key Holder Status'
                                                                title="Change Key Holder Status"/>
                        </div>
                    </div>
                </td>
                <td class='updateDate' data-id='<?php echo $id ?>'><?php echo $updated; ?></td>
            </tr>
		<?php }
	}
	?>
    </tbody>
<?php } else { ?>
    <tbody>
    <tr>
        <td class='execOpt'>nothing found</td>
    </tr>
    </tbody>
<?php } ?>
</table></div>
<div class="tableViewBtns">
    <button class="ui-corner-all ui-button downloadExcel">DL Excel</button>
    <button class="ui-corner-all ui-button refresh statBarBtn statBarRefresh exec">Refresh</button>
    <button class="ui-button ui-corner-all logout statBarBtn statBarLogout exec">Log Out</button>
</div>







