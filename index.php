<?php /** @noinspection PhpUndefinedVariableInspection */
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/19/2019
 * Time: 9:34 AM
 */

session_start();
session_regenerate_id();

require_once 'vendor/autoload.php';
require_once 'inc/htmlVars.php';

#var_dump($_SESSION);
?>

<!DOCTYPE html>
<html>
<head>
    <!--<meta charset="utf-8"/>-->
    <title>Jetro/RD Staffing </title>
    <link rel="stylesheet" type="text/css" href="<?php echo $mainCSS ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $jQueryCSS ?>">
    <link rel="stylesheet" type="text/css href="<?php echo $structureCSS ?>"">
    <link rel="icon" type="image/png" href="<?php echo $rdLogo ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $bootstrapCSS ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $fontsCSS ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $animateCSS ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $hamburgerCSS ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $select2CSS ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $cssUtil ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $displayTbls ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $slider ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $comCSS ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $statBarCSS ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $loaderCSS ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $branchProfileCSS ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $exportTableCSS ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $myCSS ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
<div id="container">
    <div id="modHeader"><h1>Jetro/RD Staffing Module</h1></div>
    <div id="login">
        <div id="branchLogInWrap" class="contact1">
            <div class="container-contact1">
                <div class="contact1-pic js-tilt rounded" data-tilt>
                    <img src="images/branchLogIn.png" alt="IMG">
                </div>

                <form id="branchForm" class="contact1-form validate-form">
				<span class="contact1-form-title">
					Branch Log-In
				</span>

                    <div class="wrap-input1 validate-input" data-validate="Employee ID is Required">
                        <input id="bEmpID" class="input1" type="text" name="empID" placeholder="Your Employee ID"
                               required>
                        <span class="shadow-input1"></span>
                    </div>

                    <div class="container-contact1-form-btn">
                        <button id="branchSubmit" class="contact1-form-btn">
						<span id="branchBtnText">
							Submit Log-In
							<i class="fa fa-long-arrow-right" aria-hidden="true"></i>
						</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div id="executiveLogInWrap" class="contact1">
            <div class="container-contact1">
                <div class="contact1-pic js-tilt rounded" data-tilt>
                    <img src="images/executiveLogIn.png" alt="IMG">
                </div>

                <form id="executiveForm" class="contact1-form validate-form" autocomplete="off">
				<span class="contact1-form-title">
					Executive Log-In
				</span>

                    <div class="wrap-input1 validate-input" data-validate="Login is required">
                        <input class="input1" type="text" name="login" placeholder="Login">
                        <span class="shadow-input1"></span>
                    </div>

                    <div class="wrap-input1 validate-input" data-validate="Valid Pin Number is Required">
                        <input class="input1" type="password" name="pin" placeholder="Pin Number">
                        <span class="shadow-input1"></span>
                    </div>

                    <div class="container-contact1-form-btn">
                        <button id="executiveSubmit" class="contact1-form-btn">
						<span id="execBtnText">
							Submit Log-In
							<i class="fa fa-long-arrow-right" aria-hidden="true"></i>
						</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="statsBar" style="display:none"></div>
    <div id="tableView" style="display:none"></div>
    <div id="todoBar" style="display:none">
        <div id="todoBarWrapper">
            <div id="execToDoSection" class="statBtmBorder">
                <div id="execToDoHeader" class="full">
                    <h3>To Do</h3>
                </div>
                <div id="toDoWrap">
                    <table class="toDoTbl">
                        <thead>
                        <th>Task</th>
                        <th>Due </br>Date</th>
                        <th># </br>To Do</th>
                        <th>% </br>>Comp</th>
                        </thead>
                        <tbody>
                        <tr>
                            <td>JCMS</td>
                            <td>Ongoing</td>
                            <td class="jcmsToDo changeable jcmsToDoNum refreshTbl">###</td>
                            <td class="jcmsCompNum refreshTbl">###</td>
                        </tr>
                        <tr class="safeLiftingToDo">
                            <td>Safe Lifting</td>
                            <td>Ongoing</td>
                            <td>???</td>
                            <td>???</td>
                        </tr>
                        <tr class="handwashingToDo">
                            <td>Hand Washing</td>
                            <td>Ongoing</td>
                            <td>???</td>
                            <td>???</td>
                        </tr>
                        <tr class="faceCoveringToDo">
                            <td>Face Covering</td>
                            <td>Ongoing</td>
                            <td>???</td>
                            <td>???</td>
                        </tr>
                        <tr class="safetyTipsToDo">
                            <td>Wkly Safety Tips</td>
                            <td>Ongoing</td>
                            <td class="changeable sftyTipComp sftyTipsNum refreshTbl">###</td>
                            <td class="sftyTipsPer refreshTbl">###</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
    </div>
</div>

<div id="exportExcelTable" class="exportExcelTable" style="display:none;"></div>

<div id='updateDialog' class='updateDialog ui-dialog ui-dialog-content center' title='Update Name'>
    <span class="filterSubTitle"></span>
    <div class="container-dialog updateDialogHTML"></div>
</div>

<div id='filterDialog' class='filterDialog ui-dialog ui-dialog-content center' title='Select Filter Choices'>
    <span class="filterSubTitle"></span>
    <div class="container-dialog filterContainer"></div>
</div>

<div id='showComDialog' class='showComDialog ui-dialog ui-dialog-content center' title='Employee Comments'>
    <span class="filterSubTitle"></span>
    <div class="comContainer container-dialog"></div>
</div>

<div id='writeComDialog' class='writeComDialog ui-dialog ui-dialog-content center' title='Add/Edit Comment'></div>

<div id='contentDialog' class='contentDialog ui-dialog ui-dialog-content center' title='Content'>
    <div class='contentDialogWrapper'><div class='jcmsAccordion'></div></div>
</div>

<div id="loadingDialog" class="loadingDialog ui-dialog ui-dialog-content center" title='Processing'>
    <div class="loader"></div>
</div>



<script src="<?php echo $fileSaverJS; ?>"></script>
<script src="<?php echo $jsXlsxJS; ?>"></script>
<script
        src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>
<script src="<?php echo $exportTableJS; ?>"></script>
<script src="<?php echo $popperJS; ?>"></script>
<script src="<?php echo $bootstrapJS; ?>"></script>
<script src="<?php echo $jqueryUiJS; ?>"></script>
<script src="<?php echo $select2JS; ?>"></script>
<script src="<?php echo $tiltJS; ?>"></script>
<script>
    $('.js-tilt').tilt({
        scale: 1.1
    })
</script>
<script src="<?php echo $mainJS ?>"></script>

</body>

</html>