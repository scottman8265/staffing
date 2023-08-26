<?php

session_start();

require_once '../class/Process.php';

$lnk = new Process();

$loggedIn    = $_SESSION['axsLevel'] === 'exec' ? $_SESSION['execData']['execName'] : null;
$loggedInNum = $_SESSION['axsLevel'] === 'exec' ? $_SESSION['execData']['execID'] : null;
$posStatus   = isset($_SESSION['filters']['status']) ? $_SESSION['filters']['status'] : null;
$execNum     = isset($_SESSION['filters']['exec']) ? $_SESSION['filters']['exec'] : null;
$branchStr   = isset($_SESSION['filters']['branchString']) ? $_SESSION['filters']['branchString'] : null;
$execStr     = null;

if ($execNum) {
	$lnk      = new Process();
	$loginQry = $lnk->query("SELECT login FROM branchInfo.opsExecs where regionID = " . $execNum);
	$execStr  = $loginQry ? $loginQry[0]['login'] : 'Exec Not Found';
}

if ($_SESSION['filters']['location'] === "location != 'WC'") {
	$locNum = "EC/MW";
}
elseif ($_SESSION['filters']['location'] !== 'location IS NOT NULL') {
	$split  = explode(' ', $_SESSION['filters']['location']);
	$locNum = str_replace(array("'", "(", ")"), '', $split[count($split) - 1]);
}
else {
	$locNum = 'Region';
}

if ($_SESSION['filters']['posClass'] === null) {
	if ($_SESSION['filters']['position'] !== 'position IS NOT NULL') {
		$split  = explode(' ', $_SESSION['filters']['position']);
		$posNum = str_replace(array("'", "(", ")"), '', $split[count($split) - 1]);
		$posQry = $lnk->query('SELECT posName FROM staffing.positions WHERE posID = ' . $posNum);

		$posNum = $posQry ? $posQry[0]['posName'] : "pos not found";
	}
	else {
		$posNum = "All";
	}
}
else {
	$posNum = $_SESSION['filters']['posClass'];
}

if ($_SESSION['filters']['status'] !== 'posStatus IS NOT NULL') {
	$split     = explode(' ', $_SESSION['filters']['status']);
	$posStatus = count($split) > 3 ? $split[2] . " " . $split[3] : $split[2];
	$posStatus = preg_replace('/\'/', '', $posStatus);
}
else {
	$posStatus = "All";
}

?>

<div id="statBarWrapper">
    <div id="statBarHeader" class="statBtmBorder">
        <h3>Welcome</h3>
        <h4><?php echo $loggedIn ?></h4>
    </div>
    <div id="filtersSection" class="statBtmBorder">
        <div id="filtersHeader"><h3>Filters</h3></div>
        <div class="filterRow half changeable filter" data-filter="filterExec"
             title="Click to Change The Executive Filter (Regional/Director/etc)">
            <div class="filterCellWrapper">
                <div class="filterCell top">Exec Filter</div>
                <div class="filterCell"><?php echo $execStr ?></div>
            </div>
            <div class="change"></div>
        </div>
        <div class="filterRow half" data-filter="filterLocation"
             title="You Cannot Change Location Right Now - Use Exec Filters Instead">
            <div class="filterCellWrapper">
                <div class="filterCell top">Location Filter</div>
                <div class="filterCell"><?php echo $locNum ?></div>
            </div>
        </div>
        <div class="filterRow half changeable filter" data-filter="filterPosition"
             title="Click to Change The Position Filter Either by Class or Position">
            <div class="filterCellWrapper">
                <div class="filterCell top">Position Filter</div>
                <div class="filterCell"><?php echo $posNum ?></div>
            </div>
            <div class="change"></div>
        </div>
        <div class="filterRow half changeable filter" data-filter="filterStatus" title="Click to Change the Status Filter">
            <div class="filterCellWrapper">
                <div class="filterCell top">Status Filter</div>
                <div class="filterCell"><?php echo $posStatus ?></div>
            </div>
            <div class="change"></div>
        </div>
        <div class="filterRow full changeable filter" data-filter="filterBranch" title="Click to Change the Branch Filter">
            <div class="filterCellWrapper">
                <div class="filterCell top">Branch Filter</div>
                <div class="filterCell">
					<?php
					if (count($_SESSION['filters']['branch']) < 10) {
						echo $_SESSION['filters']['branchString'];
					}
					else {
						echo "10 or more branches filtered";
					}
					?>
                </div>
            </div>
            <div class="change"></div>
        </div>
    </div>
    <div id="scrollableStatWrap">
        <div id="execStatsSection" class="statBtmBorder">
            <div id="execStatsHeader" class="full">
                <h3>Exec Stats</h3>
            </div>
            <div class="execStats full">
                <div class="execStatRow">
                    <div class="execStatCell title">Branches</div>
                    <div class="execStatCell count" title="Total Branches for Exec"><span
                                class="info branches">###</span>
                    </div>
                </div>
                <div class="execStatRow">
                    <div class="execStatCell title">Positions</div>
                    <div class="execStatCell count" title="Total Positions for Exec"><span
                                class="info positions">###</span>
                    </div>
                </div>
                <div class="execStatRow">
                    <div class="execStatCell title">Turnover</div>
                    <div class="execStatCell count" title="Total Turnover for Exec"><span
                                class="info turnover">???</span>
                    </div>
                </div>
                <div class="execStatRow">
                    <div class="execStatCell title">Tenure</div>
                    <div class="execStatCell count" title="Average Tenure for Exec"><span class="info tenure">???</span>
                    </div>
                </div>
            </div>
            <div class="execStats full">
                <div class="execStatRow ">
                    <div class="execStatCell title">Open Positions</div>
                    <div class="execStatCell count" title="Total Open Positions for Exec">
                        <span class="info open">###</span></div>
                </div>
                <div class="execStatRow ">
                    <div class="execStatCell title">Upgrade Positions</div>
                    <div class="execStatCell count" title="Total Upgrades for Exec">
                        <span class="info upgrade">###</span></div>
                </div>
                <div class="execStatRow ">
                    <div class="execStatCell title">In Training</div>
                    <div class="execStatCell count" title="Total In Training for Exec">
                        <span class="info training">###</span></div>
                </div>
                <div class="execStatRow ">
                    <div class="execStatCell title">On Leave</div>
                    <div class="execStatCell count" title="Total On Leave for Exec">
                        <span class="info leave">###</span></span></div>
                </div>
                <div class="execStatRow ">
                    <div class="execStatCell title">Promotable</div>
                    <div class="execStatCell count" title="Total Promotable for Exec">
                        <span class="info promotable">###</span></span></div>
                </div>
            </div>
        </div>

        <div id="execTRSection" class="statBtmBorder">
            <div id="execTRHeader" class="full">
                <h3>Talent Reef</h3>
            </div>
        </div>
    </div>
</div>
