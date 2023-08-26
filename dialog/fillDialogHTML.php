<?php

session_start();

$updateDialog = '<div id="updateStatusWrap" class="contact1">
        <div class="container-dialog updateDialogHTML">
            
        </div>
    </div>';

$filterDialog = '<span class="filterSubTitle"></span>
    <div class="container-dialog filterContainer"></div>';

$showComDialog = '<span class="filterSubTitle"></span>
    <div class="comContainer container-dialog"></div>';

$loadingDialog = '<div class="loader"></div>';

$returnArr = ['updateDialog'=>$updateDialog, 'filterDialog'=>$filterDialog, 'showComDialog'=>$showComDialog, 'loadingDialog'=>$loadingDialog, 'session'=>$_SESSION];

echo json_encode($returnArr);