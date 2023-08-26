<?php

session_start();

require_once '../class/Process.php';

$empID  = isset($_POST['empID']) ? $_POST['empID'] : null;
$certID = isset($_POST['certID']) ? $_POST['certID'] : null;
$tmName = isset($_POST['tmName']) ? $_POST['tmName'] : "TM Not Found";
$tblID = isset($_POST['tblID']) ? $_POST['tblID'] : null;

$lnk = new Process();

if (!is_null($certID)) {
	$certQry  = $lnk->query("SELECT certName FROM staffing.certTbl WHERE certID = " . $certID);
	$certName = $certQry ? $certQry[0]['certName'] : "Cert Not Found";
}

?>

<div class="addCertContainer myContainer">
    <div class="addCertHeader">
        <h5>Adding <?php echo $certName ?> Certification for </br> <?php echo $tmName ?></h5>
    </div>
    <div class="addCertWrapper myWrapper">
        <div class="addCertData" data-tblid="<?php echo $tblID ?>" data-empid="<?php echo $empID ?>" data-certid="<?php echo $certID ?>">
            <div class="branchInfoWrapper certAltName">
                <div class="branchValLabel">
                    <p>Enter Cert Name if Different From Above</p>
                </div>
                <div class="branchValInput">
                    <input class="certAltNameInp input2" type="text" name="certAltName"
                           value="<?php echo $certName ?>"/>
                </div>
            </div>
            <div class="branchInfoWrapper certIssuerName">
                <div class="branchValLabel">
                    <p>Enter Name of Certificate Issuer</p>
                </div>
                <div class="branchValInput">
                    <input class="certIssuerNameInp input2" type="text" name="certIssuerName"
                           placeholder="Issuing Agency from Certificate" autofocus/>
                </div>
            </div>
            <div class="branchInfoWrapper certAcqDate">
                <div class="branchValLabel">
                    <p>Enter Date Certificate Obtained</p>
                </div>
                <div class="branchValInput">
                    <input class="certAcqDateInp input2" type="text" name="certAcqDate"
                           placeholder="Enter 6 digit date (06/15/20 = 061520)"/>
                </div>
            </div>
        </div>

    </div>
	<div class="addCertFooter"  data-tblid="<?php echo $tblID ?>" data-empid="<?php echo $empID ?>" data-certid="<?php echo $certID ?>">
		<button class="addCertBtn submit ui-button ui-corner-all">Verify</button>
		<button class="addCertBtn cancel ui-button ui-corner-all">Cancel</button>
	</div>
</div>
