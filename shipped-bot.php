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

$config = require_once '../beru_config/config.php';

use Avaks\Beru\Order;
use Avaks\MS\OrderMS;
use Avaks\Custom\Custom;


$orders = new Order();
$ordersBeruRes = json_decode($orders->getAll('?status=PROCESSING&substatus=READY_TO_SHIP'), true);

$ordersMS = new OrderMS();
//Доставляется
$state = '327c03c6-75c5-11e5-7a40-e89700139938';
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
        $res = $orders->setMultipleOrdersStatus($ordersShipped, 'shipped');
        $message = 'ОШИБКА setMultipleOrdersStatus shipped';
        $continue = Custom::sendErrorTelegramBeru($res, $message, 'setMultipleOrdersStatusShipped');
    }
}

// fallback if failed setToShip in MS
$ordersMS = new OrderMS();
//В работе
$state = 'ecf45f89-f518-11e6-7a69-9711000ff0c4';
$ordersBeruReadyToShip = $ordersMS->getAllBeru($state);
if (isset($ordersBeruRes['orders'])){
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
//            unlink('files/labels/' . $ordersBeruRes['orders'][$key] . '.pdf');
            }
        }
    }
}




