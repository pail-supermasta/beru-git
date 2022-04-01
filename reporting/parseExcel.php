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


        //where orderData array index starts from 0
        $sheet = $spreadsheet->getSheetByName('Размещение товаров на витрине');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:Y' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {

            foreach ($data as $orderData) {

                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Размещение товаров на витрине' => (float)$orderData[17]];
                } else {
                    $plusDshSum = $orders[$orderData[0]]['Размещение товаров на витрине'] + (float)$orderData[17];
                    $orders[$orderData[0]] = ['Размещение товаров на витрине' => $plusDshSum];
                }

            }
            $orders["ordersQty"] = sizeof($data);

        }


        $sheet = $spreadsheet->getSheetByName('Складская обработка');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:Y' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {

                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Складская обработка' => (float)$orderData[17]];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Складская обработка'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Складская обработка' => (float)$orderData[17]];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Складская обработка'])) {
                    $plusDshSum = $orders[$orderData[0]]['Складская обработка'] + (float)$orderData[17];
                    $orders[$orderData[0]]['Складская обработка'] = $plusDshSum;
                }

            }
        }


        $sheet = $spreadsheet->getSheetByName('Участие в программе лояльности');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:R' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Участие в программе лояльности' => (float)$orderData[10]];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Участие в программе лояльности'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Участие в программе лояльности' => (float)$orderData[10]];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Участие в программе лояльности'])) {
                    $plusDshSum = $orders[$orderData[0]]['Участие в программе лояльности'] + (float)$orderData[10];
                    $orders[$orderData[0]]['Участие в программе лояльности'] = $plusDshSum;
                }
            }
        }

        $sheet = $spreadsheet->getSheetByName('Доставка покупателю');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:Y' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Доставка покупателю' => (float)$orderData[17]];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Доставка покупателю'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Доставка покупателю' => (float)$orderData[17]];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Доставка покупателю'])) {
                    $plusDshSum = $orders[$orderData[0]]['Доставка покупателю'] + (float)$orderData[17];
                    $orders[$orderData[0]]['Доставка покупателю'] = $plusDshSum;
                }
            }
        }

        $sheet = $spreadsheet->getSheetByName('Экспресс-доставка покупателю');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:M' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Экспресс-доставка покупателю' => (float)$orderData[5]];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Экспресс-доставка покупателю'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Экспресс-доставка покупателю' => (float)$orderData[5]];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Экспресс-доставка покупателю'])) {
                    $plusDshSum = $orders[$orderData[0]]['Экспресс-доставка покупателю'] + (float)$orderData[5];
                    $orders[$orderData[0]]['Экспресс-доставка покупателю'] = $plusDshSum;
                }
            }
        }


        $sheet = $spreadsheet->getSheetByName('Приём и перевод платежа');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:L' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {

                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Приём и перевод платежа' => (float)$orderData[4]];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Приём и перевод платежа'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Приём и перевод платежа' => (float)$orderData[4]];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Приём и перевод платежа'])) {
                    $plusDshSum = $orders[$orderData[0]]['Приём и перевод платежа'] + (float)$orderData[4];
                    $orders[$orderData[0]]['Приём и перевод платежа'] = $plusDshSum;
                }

            }
        }


        $sheet = $spreadsheet->getSheetByName('Обработка заказа в СЦ');
        $maxCell = $sheet->getHighestDataRow("O");
        $data = $sheet->rangeToArray('O3:P' . $maxCell);

        $obrabotkaZakazaSC=0;
        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                $obrabotkaZakazaSC += (float)$orderData[0];
            }
            $orders['Обработка заказа в СЦ'] = $obrabotkaZakazaSC;
            $orders['Обработка заказа в СЦ значение'] = $obrabotkaZakazaSC/$orders["ordersQty"];
        }


        $sheet = $spreadsheet->getSheetByName('Хранение невыкупов и возвратов');
        $maxCell = $sheet->getHighestDataRow("L");
        $data = $sheet->rangeToArray('L3:M' . $maxCell);

        $hranenieNevykupllZakaza=0;
        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                $hranenieNevykupllZakaza += (float)$orderData[0];
            }
            $orders['Хранение невыкупов и возвратов'] = $hranenieNevykupllZakaza;
            $orders['Хранение невыкупов и возвратов значение'] = $hranenieNevykupllZakaza/$orders["ordersQty"];
        }


        foreach ($orders as $key=>$order){

            if(!is_array($order)) continue;
            $order['dshSum'] = $order["Размещение товаров на витрине"] ?? 0;
            $order['dshSum'] += $order["Складская обработка"] ?? 0;
            $order['dshSum'] += $order["Участие в программе лояльности"] ?? 0;
            $order['dshSum'] += $order["Доставка покупателю"] ?? 0;
            $order['dshSum'] += $order["Приём и перевод платежа"] ?? 0;
            $order['dshSum'] += $order["Обработка заказа в СЦ значение"] ?? 0;
            $order['dshSum'] += $order["Хранение невыкупов и возвратов значение"] ?? 0;

            $orders[$key] = $order;
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
        $orderMS->id = null;
        $orderDetails = $orderMS->getByExternalCode();
        $orderMS->id = $orderDetails['id'];
        if(!isset($orderDetails['description'])){
            $orderDetails['description'] = '';
        }

        if(!isset($orderDSHs['Экспресс-доставка покупателю']) || $orderDSHs['Экспресс-доставка покупателю']<=0){
            $orderDSHs['Экспресс-доставка покупателю'] = 0;
        }
        if(!isset($orderDSHs['dshSum']) || $orderDSHs['dshSum']<=0){
            $orderDSHs['dshSum'] = 0;
        }

        var_dump($orderName);

        //var_dump($orderDetails['description'], $orderDSHs['dshSum'], '',$orderDSHs['Экспресс-доставка покупателю']);
        $result = $orderMS->setDSHSum($orderDetails['description'], $orderDSHs['dshSum'], '',$orderDSHs['Экспресс-доставка покупателю']);

        if (strpos($result, 'обработка-ошибок') > 0 || $result == '' || $result == false) {
            var_dump($orderMS, $result, "ERROR!!!1");
        }
    }
    return true;

}










