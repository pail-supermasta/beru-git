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
$config = require_once '../beru_config/config.php';

use Avaks\Beru\Order;
use Avaks\MS\OrderMS;



$orders = new Order();
$ordersBeruRes = json_decode($orders->getAll('?status=PROCESSING&substatus=STARTED'), true);

$ordersMS = new OrderMS();
//На выдаче
$state = '8beb25ab-6088-11e7-7a6c-d2a9003b81a4';
$ordersBeruReadyToShip = $ordersMS->getAllBeru($state);

foreach ($ordersBeruReadyToShip as $orderBeruReadyToShip) {
    $key = array_search($orderBeruReadyToShip['name'], array_column($ordersBeruRes['orders'], 'id'));
    if (!is_bool($key) && !isset($ordersBeruRes['orders'][$key]['cancelRequested'])) {

        $ordersMS->name = $ordersBeruRes['orders'][$key]['id'];
        $ordersMS->id = $orderBeruReadyToShip['id'];
        $setDeliveryRes = $orders->setDelivery($ordersBeruRes['orders'][$key]);
        //if errors continue to next order

        $orders->getSticker();
        //if errors continue to next order

        $ordersMS->setSticker();
        //if errors continue to next order

        $orders->setStatus('PROCESSING', 'READY_TO_SHIP');
        //if errors continue to next order
    }
}



