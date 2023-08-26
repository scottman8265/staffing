<?php

session_start();

$today = new DateTime();

$dayOfWeek = $today->format('w');

switch ($dayOfWeek) {
	case 5:
	case 6:
	case 0:
		$wkNum = $today->format("W");
		break;
	default:
		$wkNum = $today->format("W") - 1;
}

?>

<div class="wklySftyTpWrapper">
    <div class="wklySftyTpWkNum" data-minwk="1" data-maxwk="<?php echo $wkNum ?>">
        <div class="wkNumTxt">Select</br>Week Number</div>
        <div class="subtract arrowIcon"></div>
        <div class="wkNum"><?php echo $wkNum; ?></div>
        <div class="add arrowIcon"></div>
    </div>
    <div class="wklySftyTpWkComments">
        <input class="input1 wklySftyTipCom" type="text" placeholder="Who Did You Talk To & What Did You Talk About?">
    </div>
    <div class="wklySftyTipBtns">
        <button class="ui-button ui-corner-all wklySftyTipSubmit submit">Submit</button>
        <button class="ui-button ui-corner-all WklySftyTipCancel cancel">Cancel</button>
    </div>
</div>

