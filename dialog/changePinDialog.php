<?php

session_start();

require_once '../class/Process.php';
require_once ('../util/cleanInput.php');


$execID = isset($_POST['execID']) ? clean($_POST['execID']) : null;


?>

<div class="changePinContainer contact1">
	<div class="changePinHeader">
		<h1>Enter a 4 digit Pin Number You Will Remember</h1>
	</div>
	<div class="changePinWrapper">
		<input class="changePinInput input1" type="text" pattern="/^[0-9]+$/" placeholder="####" required/>
	</div>
	<div class="changePinSubmit" data-execid="<?php echo $execID;?>">
		<button class="submitPinChange contact1-form-btn">Submit New Pin</button>
	</div>
</div>
