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
    $ordersBeruRes = json_decode($orders->getAll('?status=PROCESSING&substatus=STARTED',$shop['orgInfo']), true);

    $ordersMS = new OrderMS();
//В работе
    $state = 'ecf45f89-f518-11e6-7a69-9711000ff0c4';
    $ordersMS->_organization = $shop['orgInfo']['organization'] ?? false;
    $ordersBeruReadyToShip = $ordersMS->getAllBeru($state);

    if (isset($ordersBeruRes['orders'])) {
        foreach ($ordersBeruReadyToShip as $orderBeruReadyToShip) {
            $key = array_search($orderBeruReadyToShip['name'], array_column($ordersBeruRes['orders'], 'id'));
            if (!is_bool($key) && !isset($ordersBeruRes['orders'][$key]['cancelRequested'])) {

                $ordersMS->name = $ordersBeruRes['orders'][$key]['id'];
                $ordersMS->id = $orderBeruReadyToShip['id'];

                $res = $orders->setDelivery($ordersBeruRes['orders'][$key],$shop['orgInfo']);
                //if errors continue to next order
                $message = 'ОШИБКА setDelivery заказа ' . $ordersMS->name;
                $continue = Custom::sendErrorTelegramBeru($res, $message, 'setDelivery');
                if ($continue) continue;

                $res = $orders->getSticker($shop['orgInfo']);
                //if errors continue to next order
                $message = 'ОШИБКА getSticker заказа ' . $ordersMS->name;
                $continue = Custom::sendErrorTelegramBeru($res, $message, 'getSticker');
                if ($continue) continue;


                $res = $ordersMS->setToShip();
                //if errors continue to next order
                $message = 'ОШИБКА setToShip заказа ' . $ordersMS->name;
                $continue = Custom::sendErrorTelegram($res, $message, 'setToShip', false, true);
                if ($continue) {
                    continue;
                } else {
//            unlink('/home/beru-service/public_html/files/labels/' . $ordersBeruRes['orders'][$key] . '.pdf');
                }

                $res = $orders->setStatus('PROCESSING', 'READY_TO_SHIP',$shop['orgInfo']);
                //if errors continue to next order
                $message = 'ОШИБКА setStatus READY_TO_SHIP заказа ' . $ordersMS->name;
                $continue = Custom::sendErrorTelegramBeru($res, $message, 'getSticker');
                if ($continue) continue;
            }
        }
    }

}

