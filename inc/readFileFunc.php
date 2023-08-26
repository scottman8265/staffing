<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/16/2019
 * Time: 6:11 AM
 */

#require_once 'vendor/autoload.php';

/**
 * @param $fileName
 * @param $ext
 * @param bool $dataOnly
 * @return \PhpOffice\PhpSpreadsheet\Spreadsheet|string|array
 */

ini_set('memory_limit', '1024M');
function readFileData($fileName) {

    $type = gettype($fileName);

    #$fileType = ucfirst($ext);

    try {
       $spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);

        /*$reader->setReadDataOnly($dataOnly);

        $spreadSheet = $reader->load($fileName);*/

        #echo 'spreadsheet count inside readFileFun: ' . gettype($spreadSheet['type']);

    }
    catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        return 'WTF is wrong with this: ' . $e->getMessage() .'?????';
    }

    return $spreadSheet ;
}