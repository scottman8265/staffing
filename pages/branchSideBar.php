<?php

session_start();

$loggedIn = isset($_SESSION['branchData']['tmName']) ? explode(' ', $_SESSION['branchData']['tmName']) : 'name not found';
$bNum     = isset($_SESSION['branchData']['tmLoc']) ? $_SESSION['branchData']['tmLoc'] : 413;

?>

<div id="statBarWrapper">
    <div id="statBarHeader">
        <div class="statBarHeader">
            <h3>Welcome</h3>
            <h4><?php echo $loggedIn[0] ?></h4>
        </div>
    </div>

    <div id="branchStatsSection">
        <div id="branchStatsHeader" class="full">
            <h3 style="margin:0;"> <?php echo $_SESSION['branchData']['tmLoc'] ?> Stats</h3>
            <p style="margin:0;"> <?php echo $_SESSION['branchData']['counts']['total'] ?> Positions</p>
        </div>
        <div class="branchStats full">
            <div class="branchStatRow half">
                <div class="branchStatCell title">Open Positions</div>
                <div class="branchStatCell count" title="Total Open Position for Branch">
                    <span class="info"><?php echo $_SESSION['branchData']['counts']['open'] ?></span></div>
            </div>
            <div class="branchStatRow half">
                <div class="branchStatCell title">Needs Upgrade</div>
                <div class="branchStatCell count" title="Total Filtered Positions Shown In Table">
                    <span class="info"><?php echo $_SESSION['branchData']['counts']['upgrade'] ?></span></div>
            </div>
        </div>
        <div class="branchStats full">
            <div class="branchStatRow half">
                <div class="branchStatCell title">In-Training</div>
                <div class="branchStatCell count" title="Total Open Position for Branch">
                    <span class="info"><?php echo $_SESSION['branchData']['counts']['inTraining'] ?></span></div>
            </div>
            <div class="branchStatRow half">
                <div class="branchStatCell title">Needs Training</div>
                <div class="branchStatCell count" title="Total Filtered Positions Shown In Table">
                    <span class="info"><?php echo $_SESSION['branchData']['counts']['needTraining'] ?></span></div>
            </div>
        </div>
        <div class="branchStats full">
            <div class="branchStatRow half">
                <div class="branchStatCell title">Promotable</div>
                <div class="branchStatCell count" title="Total Open Position for Branch">
                    <span class="info"><?php echo $_SESSION['branchData']['counts']['promotable'] ?></span></div>
            </div>
            <div class="branchStatRow half">
                <div class="branchStatCell title">Can Train</div>
                <div class="branchStatCell count" title="Total Filtered Positions Shown In Table">
                    <span class="info"><?php echo $_SESSION['branchData']['counts']['canTrain'] ?></span></div>
            </div>
        </div>
        <div class="branchStats full">
            <div class="branchStatRow half">
                <div class="branchStatCell title">Good Standing</div>
                <div class="branchStatCell count" title="Total Open Position for Branch">
                    <span class="info"><?php echo $_SESSION['branchData']['counts']['goodStanding'] ?></span></div>
            </div>
            <div class="branchStatRow half">
                <div class="branchStatCell title">On Leave</div>
                <div class="branchStatCell count" title="Total Filtered Positions Shown In Table">
                    <span class="info"><?php echo $_SESSION['branchData']['counts']['onLeave'] ?></span></div>
            </div>
        </div>
    </div>
</div>

