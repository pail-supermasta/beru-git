<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.04.2020
 * Time: 14:01
 */

error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

require_once 'vendor/autoload.php';
require_once 'src/Telegram.php';

//$config = require_once 'config.php';
$config = require_once 'config_multi.php';


use Avaks\Beru\Order;
use Avaks\MS\OrderMS;
use Avaks\Custom\Custom;

//ГОТОВО


foreach ($config['shop'] as $shop) {


    $orders = new Order();
    $ordersBeruRes = json_decode($orders->getAll('?status=PROCESSING&substatus=READY_TO_SHIP',$shop['orgInfo']), true);

    $ordersMS = new OrderMS();
//Доставляется
    $state = '327c03c6-75c5-11e5-7a40-e89700139938';
    $ordersMS->_organization = $shop['orgInfo']['organization'] ?? false;

    $ordersBeruShipping = $ordersMS->getAllBeru($state);

    $ordersShipped = array();
    if (isset($ordersBeruRes['orders'])) {
        foreach ($ordersBeruShipping as $orderBeruShipping) {
            $key = array_search($orderBeruShipping['name'], array_column($ordersBeruRes['orders'], 'id'));
            if (!is_bool($key) && !isset($ordersBeruRes['orders'][$key]['cancelRequested'])) {
                $ordersShipped[] = $ordersBeruRes['orders'][$key]['id'];
            }
        }

        if (sizeof($ordersShipped) > 0) {
            $ordersShipped = array_slice($ordersShipped, 0, 30);
            $res = $orders->setMultipleOrdersStatus($ordersShipped, 'shipped',$shop['orgInfo']);
            $message = 'ОШИБКА setMultipleOrdersStatus shipped';
            $continue = Custom::sendErrorTelegramBeru($res, $message, 'setMultipleOrdersStatusShipped');
        }
    }

// fallback if failed setToShip in MS
    $ordersMS = new OrderMS();
//В работе
    $state = 'ecf45f89-f518-11e6-7a69-9711000ff0c4';
    $ordersMS->_organization = $shop['orgInfo']['organization'] ?? false;

    $ordersBeruReadyToShip = $ordersMS->getAllBeru($state);
    if (isset($ordersBeruRes['orders'])) {
        foreach ($ordersBeruReadyToShip as $orderBeruReady) {
            $key = array_search($orderBeruReady['name'], array_column($ordersBeruRes['orders'], 'id'));
            if (!is_bool($key) && !isset($ordersBeruRes['orders'][$key]['cancelRequested'])) {
                $ordersMS->id = $orderBeruReady['id'];
                $ordersMS->name = $orderBeruReady['name'];

                $res = $ordersMS->setToShip();
                //if errors continue to next order
                $message = 'ОШИБКА setToShip заказа ' . $ordersMS->name;
                $continue = Custom::sendErrorTelegram($res, $message, 'setToShip', false, true);
                if ($continue) {
                    continue;
                } else {
//            unlink('/home/beru-service/public_html/files/labels/' . $ordersBeruRes['orders'][$key] . '.pdf');
                }
            }
        }
    }
}




