<?php /** @noinspection PhpUndefinedClassInspection */

session_start();

require_once '../class/Process.php';

function inReasonHTML() {
	$lnk     = new Process();
	$reasSql = "SELECT reasID, reasName FROM staffing.reasonTbl WHERE reasClass IN (0, 1)";
	$reasQry = $lnk->query($reasSql);

	$html = "<span class='dialog-form-title'>Choose Reason For New TM</span>
	     		<span class='dialog-form-subTitle'>[INT = Internal Move][EXT = External Move]</span>
	     		<div class='select'><select name='reasons' id='inReasons'>";

	foreach ($reasQry as $x) {
		$html .= "<option value='" . $x['reasID'] . "'>" . $x['reasName'] . "</option>";
	}

	$html .= "</select></div>";

	return $html;
}

function outReasonHTML($original) {
	$lnk     = new Process();
	$reasSql = "SELECT reasID, reasName FROM staffing.reasonTbl WHERE reasClass IN (0, 1, 2)";
	$reasQry = $lnk->query($reasSql);

	$html = "<span class='dialog-form-title'>Choose Reason For Changing</span>
	     		<span class='dialog-form-subTitle'>" . $original . "</span>
	     		<div class='select'><select name='reasons' id='outReasons'>";

	foreach ($reasQry as $x) {
		$html .= "<option value='" . $x['reasID'] . "'>" . $x['reasName'] . "</option>";
	}

	$html .= "</select></div>";

	return $html;
}

function nameHtml($id, $updateHTML, $original) {

	#$reasons = reasonHTML();

	$html = "<span class='dialog-form-title'>Enter First & Last Name</span>
	     <span class='dialog-form-subTitle'>only letters, no numbers or special characters</span>";

	if (!is_null($updateHTML)) {
		$html .= $updateHTML;
	}

	$html .= "<div class='nameInput wrap-input1 validate-input' data-validate='Name is required'>
                    <input id='update' data-id='" . $id . "' class='input1' type='text' name='name' placeholder='Team Member Name' required autofocus>
                    <span class='shadow-input1'></span>
                </div>";

	$html .= inReasonHTML();

	#echo $original;

	if (strlen($original) > 3) {
		$html .= outReasonHTML($original);
	}

	return $html;
}

function dateHtml($id, $updateHTML, $original) {
	$html = "<span class='dialog-form-title'>Select Position/Status Effective Date</span>";
	$html .= "<span class='dialog-form-subTitle'>Enter in mmddyy format only</span>";
	$html .= "<span class='dialog-form-subTitle'>(io.e. 6/1/20 == 060120)</span>";

	if (!is_null($updateHTML)) {
		$html .= $updateHTML;
	}
	$html .= "<div class='wrap-input1 validate-input' data-validate='Name is required'>
    <input id='update'  data-id='" . $id . "' class='input1' type='text' name='date' placeholder='Type 6 digit date (mmddyy)' required autofocus>
    <span class='shadow-input1'></span>
</div>";

	return $html;
}

function posStatusHtml($id, $updateHTML, $original) {

	$lnk    = new Process();
	$status = [];

	$sql = "SELECT * FROM staffing.posStatus WHERE id > 1";
	$qry = $lnk->query($sql);

	foreach ($qry as $x) {
		$status[$x['id']] = $x['statusName'];
	}

	$html = "<span class='dialog-form-title'>Select Status Below</span>
				<div class='wrap-input1 validate-input custom-select fit' data-validate='Name is required'>";
	if (!is_null($updateHTML)) {
		$html .= $updateHTML;
	}

	$html .= "<select id='update' data-id='" . $id . "'>";

	foreach ($status as $id => $statusName) {
		$html .= '<option value="' . $statusName . '">' . $statusName . '</option>';
	}

	$html .= "</select><span class='shadow-input1'></span></div>";

	$html .= "<div id='openOutReason' style='display:none'>";

	$html .= outReasonHTML('Open Status');

	$html .= "</div>";

	return $html;
}

function phoneNumHtml($id, $updateHTML, $original) {
	$html = "<span class='dialog-form-title'>Enter Phone Number</span>
<span class='dialog-form-subTitle'>format: ###-###-####</span>";
	if (!is_null($updateHTML)) {
		$html .= $updateHTML;
	}

	$html .= "<div class='wrap-input1 validate-input' data-validate='Enter Phone Number'>
    <input id='update' data-id='" . $id . "' class='input1' type='tel' name='phone' placeholder='Team Member Phone Number'
           pattern='[0-9]{3}-[0-9]{3}-[0-9]{4}' required autofocus>
    <span class='shadow-input1'></span>
</div>";

	return $html;
}

function empIDHtml($id, $updateHTML, $original) {
	$html = "<span class='dialog-form-title'>Enter Employee ID</span>";
	if (!is_null($updateHTML)) {
		$html .= $updateHTML;
	}

	$html .= "<div class='wrap-input1 validate-input' data-validate='Enter Employee ID'>
    <input id='update' data-id='" . $id . "' class='input1' type='text' name='empID' placeholder='Employee ID'
           pattern='[0-9]+' required autofocus>
    <span class='shadow-input1'></span>
</div>";

	return $html;
}

function getSliderHtml() {

	return "<div class='sliderLabel'><h4>Exec View Only?</h4></div><div class='sliderDiv''><label for='slider' class='switch'>
<input id='slider' name='execOnly' type='checkbox'>
<span class='slider round'></span></label></div>";

}

$date       = new DateTime();
$printDate  = $date->format('m/d/Y');
$field      = isset($_POST['field']) ? $_POST['field'] : null;
$id         = isset($_POST['id']) ? $_POST['id'] : null;
$original   = isset($_POST['original']) ? $_POST['original'] : null;
$updateType = isset($_POST['updateType']) ? $_POST['updateType'] : null;

if ($field === 'posStatus') {
	$updateHTML = getSliderHtml();
}
else {
	$updateHTML = null;
}

$funcCall = isset ($field) ? $field . "Html" : null;

$html = isset($funcCall) ? $funcCall($id, $updateHTML, $original) : null;

$html .= "<div data-original='" . $original . "' data-id='" . $id . "' data-field='" . $field . "' data-date='" . $printDate . "' data-updatetype='" . $_SESSION['axsLevel'] . "' class='submitBtnWrap container-contact1-form-btn'>
            	<button id='submitUpdate' class='submit contact1-form-btn'>
						<span id='submitBtnText'>Submit Update<io class='fa fa-long-arrow-right' aria-hidden='true'></io></span>
                    </button>
                </div>";

echo json_encode(['html' => $html]);


