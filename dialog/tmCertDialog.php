<?php

session_start();

require_once '../class/Process.php';

$lnk = new Process();

$tblID  = isset($_POST['tblID']) ? $_POST['tblID'] : null;
$empID  = isset($_POST['empID']) ? $_POST['empID'] : null;
$tmName = isset($_POST['tmName']) ? $_POST['tmName'] : null;

/**
 * @param $lnk Process
 *
 * @return string
 */
function getCertOptHTML($lnk) {
	$certQry = $lnk->query("SELECT * FROM staffing.certTbl");

	$html  = "<select name='certSelection' class='certSelection input2'>";
	$certs = [];

	foreach ($certQry as $cert) {
		$html                   .= "<option value='" . $cert['certID'] . "'>" . $cert['certName'] . "</option>";
		$certs[$cert['certID']] = $cert['certName'];
	}

	$html .= "</select>";

	return [$html, $certs];
}

/**
 * @param $lnk   Process
 * @param $empID integer
 *
 * @return array
 */
function getTmCert($lnk, $empID) {
	$tmCertSql = "SELECT * FROM staffing.tmCerts WHERE certActive = 1 && empID = " . $empID;
	$tmCertQry = $lnk->query($tmCertSql);

	$certs = [];

	if ($tmCertQry) {
		foreach ($tmCertQry as $x) {
			$certs[$x['certID']][] = ['issuer'   => $x['certIssuer'],
			                          'acquired' => $x['certAcq'],
			                          'name'     => $x['certName'],
			                          'expires'  => $x['certExp']];
		}
	}

	#var_dump($certs);

	return $certs;

}

$certOpts = getCertOptHTML($lnk);

$optionHTML = $certOpts[0];
$certNames  = $certOpts[1];

$tmCerts = !is_null($empID) ? getTmCert($lnk, $empID) : null;

?>

<div class="tmCertContainer myContainer">
    <div class="tmCertHeader">
        <div class="tmCertOptions"><?php echo $optionHTML ?></div>
        <div class="tmCertSubmit" data-tblid="<?php echo $tblID ?>" data-empid="<?php echo $empID ?>" data-tmname="<?php echo $tmName ?>">
            <button class="ui-button ui-corner-all addCert">Add Cert</button>
        </div>
    </div>
    <div class="tmCertWrapper myWrapper">
        <table class="tmCertData">
	        <thead class="tmCertDataHeader">
		        <th>Cert Name</th>
		        <th>Cert Issuer</th>
		        <th>Cert Acquired</th>
		        <th>Cert Expires</th>
	        </thead><tbody>

			<?php if (!empty($tmCerts) && !is_null($tmCerts)) {
				foreach ($tmCerts as $y => $x) { ?>

					<?php foreach ($x as $k => $z) { ?>
                        <tr class="tmCertRow">
                            <td class="tmCertCell certName"><?php echo $z['name'] ?></td>
                            <td class="tmCertCell certIssuer"><?php echo $z['issuer'] ?></td>
                            <td class="tmCertCell certAcq"><?php echo $z['acquired'] ?></td>
                            <td class="tmCertCell certExp"><?php echo $z['expires'] ?></td>
                        </tr>
					<?php }
				}
			}
			else { ?>
                <tr class="tmCertRow"><h1>No Certifications Found</h1></tr><?php } ?>
	        </tbody></table>
    </div>
</div>
