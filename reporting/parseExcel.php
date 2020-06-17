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

require_once '../vendor/autoload.php';


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Avaks\MS\OrderMS;

$spreadsheet = new Spreadsheet();

$directory = 'files/new';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
foreach ($scanned_directory as $file) {
    $resultGetDSH = getDSH($file);
    $result = setDSH($resultGetDSH);

    if ($result) {
        rename("files/new/$file", "files/old/$file");
    }

}


function getDSH($inputFileName)
{
    $orders = [];


    try {
        $spreadsheet = IOFactory::load("files/new/$inputFileName");
    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        die('Error loading file: ' . $e->getMessage());
    }

    try {


        $sheet = $spreadsheet->getSheetByName('Размещение товаров на Беру');;
        $maxCell = $sheet->getHighestDataRow("A");
        $data = $sheet->rangeToArray('A2:Q' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {

            foreach ($data as $orderData) {


                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Размещение товаров на Беру' => (float)$orderData[16]];
                } else {
                    $plusDshSum = $orders[$orderData[0]]['Размещение товаров на Беру'] + (float)$orderData[16];
                    $orders[$orderData[0]] = ['Размещение товаров на Беру' => $plusDshSum];

                }

                $orders[$orderData[0]] = $orders[$orderData[0]] + ['dshSum' => $orders[$orderData[0]]['Размещение товаров на Беру']];
            }

        }


        $sheet = $spreadsheet->getSheetByName('Агентское вознаграждение');
        $maxCell = $sheet->getHighestDataRow("A");
        $data = $sheet->rangeToArray('A2:E' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {


                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Агентское вознаграждение' => (float)$orderData[4]];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Агентское вознаграждение'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Агентское вознаграждение' => (float)$orderData[4]];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Агентское вознаграждение'])) {
                    $plusDshSum = $orders[$orderData[0]]['Агентское вознаграждение'] + (float)$orderData[4];
                    $orders[$orderData[0]]['Агентское вознаграждение'] = $plusDshSum;
                }

                if (!isset($orders[$orderData[0]]['dshSum'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['dshSum' => $orders[$orderData[0]]['Агентское вознаграждение']];
                } else {
                    $orders[$orderData[0]]['dshSum'] += $orders[$orderData[0]]['Агентское вознаграждение'];
                }


            }


        }
        return $orders;

    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        die('Error getActiveSheet: ' . $e->getMessage());
    }


}

function setDSH($orders)
{


    $orderMS = new OrderMS();


    foreach ($orders as $orderName => $orderDSHs) {

        $orderMS->name = $orderName;
        $orderDetails = $orderMS->getByName();
        $orderMS->id = $orderDetails['id'];

//        $razmeshComment = isset($orderDSHs['Размещение товаров на Беру']) ? "Размещение товаров на Беру: " . $orderDSHs['Размещение товаров на Беру'] : '';
//        $agentComment = isset($orderDSHs['Агентское вознаграждение']) ? "Агентское вознаграждение: " . $orderDSHs['Агентское вознаграждение'] : '';

//        $DSHSumComment = "\n$razmeshComment $agentComment";

        $result = $orderMS->setDSHSum($orderDetails['description'], $orderDSHs['dshSum'], '');
        var_dump($orderName, $orderDSHs['dshSum']);

        if (strpos($result, 'обработка-ошибок') > 0 || $result == '' || $result == false) {
            var_dump($orderName, $result, "ERROR!!!1");
            die();
        }
    }
    return true;

}










