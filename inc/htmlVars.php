<?php

$_SESSION            = [];
$_SESSION['filters'] = ["branch"   => "branch IS NOT NULL",
                        "status"   => "status IS NOT NULL",
                        "position" => "position IS NOT NULL",
                        "location" => "location IS NOT NULL",
                        "execName" => null,
                        "regional" => "regional IS NOT NULL",
                        "director" => "director IS NOT NULL",
                        "posClass" => null];

if (file_exists("css/main.css")) {
	$mainCSS           = "css/main.css";
	$jQueryCSS         = "jQuery/jquery-ui.css";
	$themeCSS          = "jQuery/jquery-ui.theme.css";
	$structureCSS      = "jQuery/jquery-ui.structure.css";
	$jQuery            = 'src="https://code.jquery.com/jquery-3.3.1.js"
	  integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
	  crossorigin="anonymous"';
	$jQueryUI          = "jQuery/jquery-ui.js";
	$mainJS            = "js/main.js";
	$rdLogo            = "images/icons/rdLogo1.png";
	$bootstrapCSS      = "vendor/bootstrap/css/bootstrap.min.css";
	$fontsCSS          = "fonts/font-awesome-4.7.0/css/font-awesome.min.css";
	$animateCSS        = "vendor/animate/animate.css";
	$hamburgerCSS      = "vendor/css-hamburgers/hamburgers.min.css";
	$select2CSS        = "vendor/select2/select2.min.css";
	$cssUtil           = "css/util.css";
	$myCSS             = "css/myCSS.css";
	$jqueryJS          = "vendor/jquery/jquery-3.2.1.min.js";
	$popperJS          = "vendor/bootstrap/js/popper.js";
	$bootstrapJS       = "vendor/bootstrap/js/bootstrap.min.js";
	$select2JS         = "vendor/select2/select2.min.js";
	$tiltJS            = "vendor/tilt/tilt.jquery.min.js";
	$jqueryUiJS        = "jQuery/jquery-ui.js";
	$displayTbls       = "css/displayTbls.css";
	$slider            = "css/slider.css";
	$comCSS            = "css/comCSS.css";
	$statBarCSS        = "css/statBar.css";
	$loaderCSS            = "css/loader.css";
	$bootstrapBundleJS = "js/bootstrapBundle.js";
	$branchProfileCSS = "css/branchProfile.css";
	$exportTableCSS = "js/TableExport/src/stable/css/tableexport.css";
	$fileSaverJS = "js/FileSaver/src/FileSaver.js";
	$exportTableJS = "js/TableExport/src/stable/js/tableexport.js";
	$jsXlsxJS = "node_modules/js-xlsx/dist/xlsx.core.min.js";
}
else {
	echo "File Does Not Exist";
	$mainCSS           = "../css/main.css";
	$jQueryCSS         = "../jQuery/jquery-ui.css";
	$themeCSS          = "../jQuery/jquery-ui.theme.css";
	$structureCSS      = "../jQuery/jquery-ui.structure.css";
	$jQuery            = 'src="https://code.jquery.com/jquery-3.3.1.js"
	  integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
	  crossorigin="anonymous"';
	$jQueryUI          = "../jQuery/jquery-ui.js";
	$mainJS            = "../js/main.js";
	$rdLogo            = "../images/icons/rdLogo1.png";
	$bootstrapCSS      = "../vendor/bootstrap/css/bootstrap.min.css";
	$fontsCSS          = "../fonts/font-awesome-4.7.0/css/font-awesome.min.css";
	$animateCSS        = "../vendor/animate/animate.css";
	$hamburgerCSS      = "../vendor/css-hamburgers/hamburgers.min.css";
	$select2CSS        = "../vendor/select2/select2.min.css";
	$cssUtil           = "../css/util.css";
	$myCSS             = "../css/myCSS.css";
	$jqueryJS          = "../vendor/jquery/jquery-3.2.1.min.js";
	$popperJS          = "../vendor/bootstrap/js/popper.js";
	$bootstrapJS       = "../vendor/bootstrap/js/bootstrap.min.js";
	$select2JS         = "../vendor/select2/select2.min.js";
	$tiltJS            = "../vendor/tilt/tilt.jquery.min.js";
	$jqueryUiJS        = "../jQuery/jquery-ui.js";
	$displayTbls       = "../css/displayTbls.css";
	$slider            = "../css/slider.css";
	$comCSS            = "../css/comCSS.css";
	$statBarCSS        = "../css/statBar.css";
	$loaderCSS         = "../css/loader.css";
	$bootstrapBundleJS = "../js/bootstrapBundle.js";
	$branchProfileCSS = "../css/branchProfile.css";
	$exportTableCSS = "../js/TableExport/src/stable/css/tableexport.css";
	$fileSaverJS = "../js/FileSaver/src/FileSaver.js";
	$exportTableJS = "../js/TableExport/src/stable/js/tableexport.js";
	$jsXlsxJS = "../node_modules/js-xlsx/dist/xlsx.core.min.js";
}