<?php /** @noinspection PhpUndefinedClassInspection */

date_default_timezone_set('America/Chicago');

session_start();

$_SESSION['axsLevel'] = 'branch';

$needProcess = false;
require_once("../vendor/autoload.php");
require_once("../class/Process.php");


use PhpOffice\PhpSpreadsheet\Reader\Exception;

$name     = isset($_SESSION['branchData']['tmName']) ? $_SESSION['branchData']['tmName'] : null;
$pos      = isset($_SESSION['branchData']['tmPos']) ? $_SESSION['branchData']['tmPos'] : null;
$bNum     = isset($_SESSION['branchData']['tmLoc']) ? $_SESSION['branchData']['tmLoc'] : null;
$empID    = isset($_SESSION['branchData']['tmID']) ? $_SESSION['branchData']['tmID'] : null;
$bName    = isset($_SESSION['branchData']['tmBranch']) ? $_SESSION['branchData']['tmBranch'] : null;
$nickName = isset($_SESSION['branchData']['tmNick']) ? $_SESSION['branchData']['tmNick'] : null;

$z    = [];
$info = [];

function logAccess($name, $pos, $bNum, $empID) {
	$lnk = new Process();

	$date    = new DateTime();
	$axsDate = $date->format('Y-m-d H:i:s');

	$_SESSION['axsTime'] = $axsDate;

	$sql    = "INSERT INTO staffing.dbAccess (axsBranch, axsName, axsPos, axsLevel, axsDate, axsEmpID, axsModule, axsSession) VALUES (?, ?, ?, 'branch', ?, ?, 1, ?)";
	$params = [$bNum, $name, $pos, $axsDate, $empID, session_id()];
	$qry    = $lnk->query($sql, $params);

	$axsID             = $lnk->getLastID();
	$_SESSION['axsID'] = $axsID;
}

function clean($value) {

	// replaces strings < 3 with a '-'
	if (strlen($value) < 3) {
		$value = "-";
	}
	else {
		// If magic quotes not turned on add slashes.
		if (!get_magic_quotes_gpc()) {
			$value = addslashes($value);
		}

		// Strip any tags from the value.
		$value = strip_tags($value);

		// replaces extra spaces & tabs & new lines with single space
		$value = preg_replace('/\s+/', ' ', $value);
		$value = preg_replace('/\t+/', ' ', $value);
		$value = preg_replace('/\n\r+/', ' ', $value);

		//converts to iso-8859 format
		#$value = utf8_decode($value);

		// trims white spaces from beginning & end
		$value = trim($value);
	}

	// Return the value out of the function.
	return $value;
}

function getPositions($bNum) {
	$lnk     = new Process();
	$pos     = [];
	$profile = [];
	$posSql  = "SELECT posID, posName FROM staffing.positions ORDER BY viewOrder ASC";
	$posQry  = $lnk->query($posSql);
	foreach ($posQry as $a => $b) {
		$pos[$b['posID']] = $b['posName'];
	}

	$profSql    = "SELECT posProfile FROM branchInfo.branches where branchNum = ?";
	$profParams = [$bNum];
	$profQry    = $lnk->query($profSql, $profParams);

	#var_dump($profQry[0]['posProfile']);

	$posProfile = unserialize($profQry[0]['posProfile']);

	foreach ($pos as $positionID => $positionName) {
		if (isset($posProfile[$positionID])) {
			for ($i = 0; $i < $posProfile[$positionID]; $i ++) {
				$profile[$positionID][] = ['posName' => $positionName];
			}
		}
	}

	return $profile;
}

function getInfoArray($bNum, $pos) {
	$lnk        = new Process();
	$info       = [];
	$branchInfo = [];
	$open       = $upgrade = $goodStanding = $inTraining = $needTraining = $onLeave = $promotable = $canTrain = $total = 0;

	$branchSql    = "SELECT id, position, empID, tmName, dateInPos, posStatus, updated, phoneNum FROM staffing.branchStaffing WHERE branchNum = ?";
	$branchParams = [$bNum];
	$branchQry    = $lnk->query($branchSql, $branchParams);

	foreach ($branchQry as $k => $v) {

		$total ++;

		$branchInfo[$v['position']][] = ['tblID'   => clean($v['id']),
		                                 'empID'   => clean($v['empID']),
		                                 'tmName'  => clean($v['tmName']),
		                                 'since'   => clean($v['dateInPos']),
		                                 'status'  => clean($v['posStatus']),
		                                 'updated' => clean($v['updated']),
		                                 'phone'   => clean($v['phoneNum'])];

		switch ($v['posStatus']) {
			case 'Open':
				$open ++;
				break;
			case 'Good Standing':
				$goodStanding ++;
				break;
			case 'In Training':
				$inTraining ++;
				break;
			case 'Need Upgrade':
				$upgrade ++;
				break;
			case 'Promotable':
				$promotable ++;
				break;
			case 'Can Train':
				$canTrain ++;
				break;
			case 'More Training':
				$needTraining ++;
				break;
			case 'On Leave':
				$onLeave ++;
				break;
		}

		$_SESSION['branchData']['counts'] = ['open'         => $open,
		                                     'upgrade'      => $upgrade,
		                                     'goodStanding' => $goodStanding,
		                                     'inTraining'   => $inTraining,
		                                     'canTrain'     => $canTrain,
		                                     'promotable'   => $promotable,
		                                     'needTraining' => $needTraining,
		                                     'onLeave'      => $onLeave,
		                                     'total'        => $total];


	}

	foreach ($pos as $positionID => $positions) {
		foreach ($positions as $key => $info) {
			if (isset($branchInfo[$positionID][$key])) {
				$pos[$positionID][$key] += $branchInfo[$positionID][$key];
			}
		}
	}

	#var_dump($pos);

	return $pos;
}

function createTblHeader($header) {
	global $tblHtml;

	$tblHtml .= "<th class='sort'>" . $header . "</th>";
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

logAccess($name, $pos, $bNum, $empID);

$branchHeaders = ['Position',
                  'Name',
                  'Employee ID',
                  'Status',
                  'Date In Position',
                  'Phone Number',
                  'Info',
                  'Last Updated'];
$branchFooters = ['Exec',
                  'Branch',
                  'Status',
                  'Position',
                  'Level',
                  'Upcoming',
                  'Upcoming',
                  'Upcoming',
                  'Log Out'];

$posProfile = getPositions($bNum);
$info       = getInfoArray($bNum, $posProfile);

#var_dump($_SESSION);

$tblHtml = '<div id="tableWrapper"><table class="paleBlueRows" data-updatetype="branch"><thead>';
array_map("createTblHeader", $branchHeaders);
$tblHtml .= "</thead>";
/*$tblHtml .= "</thead><tfoot>";
array_map("createTblFooter", $branchFooters);
$tblHtml   .= "</tfoot>";*/
$lineCount = 1;
echo $tblHtml;
?>
<tbody>
<?php
foreach ($info as $posID => $keys) {
	foreach ($keys as $key => $data) {
		$posName   = !empty($data['posName']) ? $data['posName'] : "-";
		$tmName    = isset($data['tmName']) ? $data['tmName'] : "-";
		$posStatus = isset($data['status']) ? $data['status'] : "-";
		$since     = isset($data['since']) ? $data['since'] : "-";
		$updated   = isset($data['updated']) ? $data['updated'] : "-";
		$phone     = isset($data['phone']) ? $data['phone'] : "-";
		$empID     = isset($data['empID']) ? $data['empID'] : "-";
		$tblID     = isset($data['tblID']) ? $data['tblID'] : "-";

		?>
        <tr id="<?php echo $tblID ?>">
            <td class='position'><?php echo $posName; ?></td>
            <td data-field="name" class='name changeable update'><?php echo $tmName; ?></td>
            <td data-field="empID" class='empID changeable update'><?php echo $empID; ?></td>
            <td data-field="posStatus" class='posStatus changeable update'><?php echo $posStatus; ?></td>
            <td data-field="date" class='date changeable update'><?php echo $since; ?></td>
            <td data-field="phoneNum" class='phoneNum changeable update'><?php echo $phone; ?></td>
            <td class='iconContainer' data-id='<?php echo $tblID ?>'>
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
            <td class='updateDate'><?php echo $updated; ?></td>
        </tr>
	<?php }
} ?>
</tbody>
</table>
</div>
<div class="tableViewBtns" data-bnum="<?php echo $bNum ?>" >
    <button class="ui-corner-all ui-button wklySafetyTip">Weekly Safety Tip</button>
    <button class="ui-corner-all ui-button refresh statBarBtn statBarRefresh branch">Refresh</button>
    <button class="ui-button ui-corner-all logout statBarBtn statBarLogout exec">Log Out</button>
</div>


