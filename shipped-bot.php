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
$ordersBeruRes = json_decode($orders->getAll('?status=PROCESSING&substatus=READY_TO_SHIP'), true);

$ordersMS = new OrderMS();
//Доставляется
$state = '327c03c6-75c5-11e5-7a40-e89700139938';
$ordersBeruShipping = $ordersMS->getAllBeru($state);

$ordersShipped = array();
foreach ($ordersBeruShipping as $orderBeruShipping) {
    $key = array_search($orderBeruShipping['name'], array_column($ordersBeruRes['orders'], 'id'));
    if (!is_bool($key) && !isset($ordersBeruRes['orders'][$key]['cancelRequested'])) {

        $ordersShipped[]  = $ordersBeruRes['orders'][$key]['id'];

    }
}

$orders->setMultipleOrdersStatus($ordersShipped);

