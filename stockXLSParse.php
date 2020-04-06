<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.12.2019
 * Time: 13:29
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

header('Content-Type: application/json');

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Avaks\Stocks;

$spreadsheet = new Spreadsheet();


function getStock($barcode)
{
    $stocks = new Stocks();
    $stocks->getMPNFFByBarcode($barcode);
    if ($stocks->found == false) return false;
    return $stocks->available;
}

$directory = 'files';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
foreach ($scanned_directory as $file) {
    try {
        $result = processFile($file);
    } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
    }
    if ($result != false) {
        echo 'processFile done';
    }

}


/**
 * @param $inputFileName
 * @return bool
 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 */
function processFile($inputFileName)
{
    try {
        $spreadsheet = IOFactory::load("files/$inputFileName");
    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        die('Error loading file: ' . $e->getMessage());
    }

    try {
        $sheet = $spreadsheet->getActiveSheet();
        $maxCell = $sheet->getHighestDataRow("I");
        $data = $sheet->rangeToArray('I5:AH' . $maxCell);
    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        die('Error getActiveSheet: ' . $e->getMessage());
    }

    if (isset($data) && sizeof($data) > 0) {
        foreach ($data as $index => $product) {
            $index += 5;
            $barcode = $product[0];
//            $oldStock = $product[25];
            $stockRes = getStock($barcode);
            if ($stockRes != false) {
               $spreadsheet->getActiveSheet()
                    ->getCell("AH$index")
                    ->setValue($stockRes);
            }

        }
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($inputFileName);

        return true;

    } else {
        return false;
    }


}