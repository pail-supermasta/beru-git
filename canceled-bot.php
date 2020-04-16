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
$ordersBeruRes = json_decode($orders->getAll('?status=PROCESSING'), true);

$ordersMS = new OrderMS();
//Отменен
$state = '327c070c-75c5-11e5-7a40-e8970013993b';
$ordersBeruCanceled = $ordersMS->getAllBeru($state);

$ordersCanceled = array();

foreach ($ordersBeruCanceled as $orderBeruCanceled) {
    $key = array_search($orderBeruCanceled['name'], array_column($ordersBeruRes['orders'], 'id'));
    if (!is_bool($key) && !isset($ordersBeruRes['orders'][$key]['cancelRequested'])) {
        $ordersCanceled[] = $orderBeruCanceled['name'];

    }
}

var_dump($ordersCanceled);
if (sizeof($ordersCanceled) > 0) {
    $orders->setMultipleOrdersStatus($ordersCanceled, 'canceled');
}
