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


foreach ($config['shop'] as $shop) {
    /*set canceled in Beru if Отменен in MS*/

    $orders = new Order();
    $ordersBeruRes = json_decode($orders->getAll('?status=PROCESSING',$shop['orgInfo']), true);

    $ordersMS = new OrderMS();
//Отменен
    $state = '327c070c-75c5-11e5-7a40-e8970013993b';
    $ordersMS->_organization = $shop['orgInfo']['organization'] ?? false;

    $ordersBeruCanceled = $ordersMS->getAllBeru($state);

    $ordersCanceled = array();
    if (sizeof($ordersBeruCanceled) > 0 && isset($ordersBeruRes['orders'])) {

        foreach ($ordersBeruCanceled as $orderBeruCanceled) {
            $key = array_search($orderBeruCanceled['name'], array_column($ordersBeruRes['orders'], 'id'));
            if (!is_bool($key) && !isset($ordersBeruRes['orders'][$key]['cancelRequested'])) {
                $ordersCanceled[] = $orderBeruCanceled['name'];
            }
        }

        if (sizeof($ordersCanceled) > 0) {
            $ordersCanceled = array_slice($ordersCanceled, 0, 30);
            $res = $orders->setMultipleOrdersStatus($ordersCanceled, 'canceled',$shop['orgInfo']);
            $message = 'ОШИБКА setMultipleOrdersStatus canceled';
            $continue = Custom::sendErrorTelegramBeru($res, $message, 'setMultipleOrdersStatusCanceled');
        }
    }
}

