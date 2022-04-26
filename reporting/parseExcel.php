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

        /*NEW DSH SUM*/

        //where orderData array index starts from 0
        $sheet = $spreadsheet->getSheetByName('Размещение товаров на витрине');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:Z' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {

            foreach ($data as $orderData) {
                $col = (float)$orderData[18];

                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Размещение товаров на витрине' => $col];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Размещение товаров на витрине'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Размещение товаров на витрине' => $col];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Размещение товаров на витрине'])) {
                    $plusDshSum = $orders[$orderData[0]]['Размещение товаров на витрине'] + $col;
                    $orders[$orderData[0]]['Размещение товаров на витрине'] = $plusDshSum;
                }
            }
        }


        $sheet = $spreadsheet->getSheetByName('Участие в программе лояльности');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:R' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                $col = (float)$orderData[10];

                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Участие в программе лояльности' => $col];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Участие в программе лояльности'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Участие в программе лояльности' => $col];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Участие в программе лояльности'])) {
                    $plusDshSum = $orders[$orderData[0]]['Участие в программе лояльности'] + $col;
                    $orders[$orderData[0]]['Участие в программе лояльности'] = $plusDshSum;
                }
            }
        }

        $sheet = $spreadsheet->getSheetByName('Расходы на рекламные кампании');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:Q' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                $col = (float)$orderData[9];

                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Расходы на рекламные кампании' => $col];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Расходы на рекламные кампании'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Расходы на рекламные кампании' => $col];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Расходы на рекламные кампании'])) {
                    $plusDshSum = $orders[$orderData[0]]['Расходы на рекламные кампании'] + $col;
                    $orders[$orderData[0]]['Расходы на рекламные кампании'] = $plusDshSum;
                }
            }
        }

        $sheet = $spreadsheet->getSheetByName('Рассрочка');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:Q' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                $col = (float)$orderData[9];

                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Рассрочка' => $col];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Рассрочка'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Рассрочка' => $col];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Рассрочка'])) {
                    $plusDshSum = $orders[$orderData[0]]['Рассрочка'] + $col;
                    $orders[$orderData[0]]['Рассрочка'] = $plusDshSum;
                }
            }
        }

        /*COST LOG SUM*/

        $sheet = $spreadsheet->getSheetByName('Доставка покупателю');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:AA' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                $col = (float)$orderData[19];

                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Доставка покупателю' => $col];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Доставка покупателю'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Доставка покупателю' => $col];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Доставка покупателю'])) {
                    $plusCostLogSum = $orders[$orderData[0]]['Доставка покупателю'] + $col;
                    $orders[$orderData[0]]['Доставка покупателю'] = $plusCostLogSum;
                }
            }
        }

        $sheet = $spreadsheet->getSheetByName('Экспресс-доставка покупателю');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:Y' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                $col = (float)$orderData[17];

                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Экспресс-доставка покупателю' => $col];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Экспресс-доставка покупателю'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Экспресс-доставка покупателю' => $col];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Экспресс-доставка покупателю'])) {
                    $plusCostLogSum = $orders[$orderData[0]]['Экспресс-доставка покупателю'] + $col;
                    $orders[$orderData[0]]['Экспресс-доставка покупателю'] = $plusCostLogSum;
                }
            }
        }


        $sheet = $spreadsheet->getSheetByName('Хранение невыкупов и возвратов');
        $maxCell = $sheet->getHighestDataRow("J");
        $data = $sheet->rangeToArray('J3:P' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                $col = (float)$orderData[6];

                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Хранение невыкупов и возвратов' => $col];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Хранение невыкупов и возвратов'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Хранение невыкупов и возвратов' => $col];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Хранение невыкупов и возвратов'])) {
                    $plusCostLogSum = $orders[$orderData[0]]['Хранение невыкупов и возвратов'] + $col;
                    $orders[$orderData[0]]['Хранение невыкупов и возвратов'] = $plusCostLogSum;
                }
            }
        }

        /*COST PAYMENTS*/

        $sheet = $spreadsheet->getSheetByName('Приём и перевод платежа');
        $maxCell = $sheet->getHighestDataRow("H");
        $data = $sheet->rangeToArray('H3:L' . $maxCell);

        if (isset($data) && sizeof($data) > 0) {
            foreach ($data as $orderData) {
                $col = (float)$orderData[4];

                if (!isset($orders[$orderData[0]])) {
                    $orders[$orderData[0]] = ['Приём и перевод платежа' => $col];
                } elseif (isset($orders[$orderData[0]]) && !isset($orders[$orderData[0]]['Приём и перевод платежа'])) {
                    $orders[$orderData[0]] = $orders[$orderData[0]] + ['Приём и перевод платежа' => $col];
                } elseif (isset($orders[$orderData[0]]) && isset($orders[$orderData[0]]['Приём и перевод платежа'])) {
                    $plusCostPayments = $orders[$orderData[0]]['Приём и перевод платежа'] + $col;
                    $orders[$orderData[0]]['Приём и перевод платежа'] = $plusCostPayments;
                }
            }
        }


        /*FINAL ITERATION*/


        foreach ($orders as $key => $order) {

            if (!is_array($order)) continue;
            $order['dshSum'] = $order["Размещение товаров на витрине"] ?? 0;
            $order['dshSum'] += $order["Участие в программе лояльности"] ?? 0;
            $order['dshSum'] += $order["Расходы на рекламные кампании"] ?? 0;
            $order['dshSum'] += $order["Рассрочка"] ?? 0;

            $order['logSum'] = $order["Доставка покупателю"] ?? 0;
            $order['logSum'] += $order["Экспресс-доставка покупателю"] ?? 0;
            $order['logSum'] += $order["Хранение невыкупов и возвратов"] ?? 0;

            $order['costPayments'] = $order["Приём и перевод платежа"] ?? 0;


            $orders[$key] = $order;
        }
        return $orders;


    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        die('Error getActiveSheet: ' . $e->getMessage());
    }


}

function setDSH($orders)
{
    $result = false;


    $orderMS = new OrderMS();


    foreach ($orders as $orderName => $orderDSHs) {

        $orderMS->name = $orderName;
        $orderMS->id = null;
        $orderDetails = $orderMS->getByExternalCode();
        $orderMS->id = $orderDetails['id'];
        if (!isset($orderDetails['description'])) {
            $orderDetails['description'] = '';
        }

        if (!isset($orderDSHs['dshSum']) || $orderDSHs['dshSum'] <= 0) {
            $orderDSHs['dshSum'] = 0;
        }

        if (!isset($orderDSHs['logSum']) || $orderDSHs['logSum'] <= 0) {
            $orderDSHs['logSum'] = 0;
        }

        $DSHSumComment = "";
        if (isset($orderDSHs['costPayments']) && $orderDSHs['costPayments'] > 0) {
            $DSHSumComment = "\r\nCost payments: " . $orderDSHs['costPayments'];
        }

        $inDelivery = '327c03c6-75c5-11e5-7a40-e89700139938';
        $delivered = '8beb27b2-6088-11e7-7a6c-d2a9003b81a5';
        $search = $orderDetails['state']['meta']['href'];

        if (strpos($search, $inDelivery) > 0 || strpos($search, $delivered) > 0) {
            var_export('30 added');
            $orderDSHs['logSum'] += 30;
        }


//        var_export("CHECK DSH AND LOG SUMs FIRST, THEN COMMENT THIS!");
//        var_dump("DSHSumNum: " . $orderDSHs['dshSum']);
//        var_dump("LogisticSumNum: " . $orderDSHs['logSum']);
//        var_dump($DSHSumComment);
//        var_dump($orderMS->name);
//        die();


        $result = $orderMS->setDSHSumAndLogisticSum(
            $orderDetails['description'],
            $orderDSHs['dshSum'],
            $orderDSHs['logSum'],
            $DSHSumComment
        );

        if (strpos($result, 'обработка-ошибок') > 0 || $result == '') {
            var_dump($orderMS->name . " has just caused an error. Cannot proceed any further!");
            die();
        }

        var_dump($orderMS->name);
        var_dump("DSHSumNum: " . $orderDSHs['dshSum']);
        var_dump("LogisticSumNum: " . $orderDSHs['logSum']);
        var_dump($DSHSumComment);
    }
    return $result;

}










