<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.05.2020
 * Time: 12:48
 */

error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

require_once 'vendor/autoload.php';
require_once 'src/Telegram.php';

$config = require_once 'config.php';

use Avaks\Beru\Order;
use Avaks\MS\OrderMS;
use Avaks\Custom\Custom;

date_default_timezone_set('Europe/Moscow');


function compactOrders($ordersBeruResp)
{
    foreach ($ordersBeruResp as $orderBeru) {
        $orders[$orderBeru['id']] = $orderBeru['status'];
    }
    return $orders;
}

// 4 days
$offset = 94 * 60 * 60;
$create_day = date("d-m-Y", strtotime(gmdate("d-m-Y")) - $offset);

//get all orders for 4 days from beru
$ordersBeruInstatce = new Order();

$fromDate = "?fromDate=$create_day";
$ordersBeru = $ordersBeruInstatce->getAll($fromDate);
$ordersBeru = json_decode($ordersBeru, true);
if ($ordersBeru['pager']['pagesCount'] > 1) {
    $pageCount = $ordersBeru['pager']['pagesCount'];

    $compactedBeruOrders = [];
    for ($i = 1; $i <= $pageCount; $i++) {
        $variables = "?" . $fromDate . "&page=$i";
        $ordersBeruPage = $ordersBeruInstatce->getAll($variables);
        $ordersBeruPage = json_decode($ordersBeruPage, true);
        $compactedBeruOrders = compactOrders($ordersBeruPage['orders']) + $compactedBeruOrders;
    }

} else {
    $compactedBeruOrders = compactOrders($ordersBeru['orders']);
};


// get get all orders for 7 days from MS
$offset = 94 * 60 * 60;
$gte = date("Y-m-d", strtotime(date("Y-m-d")) - $offset);
$period = ['$gte' => $gte];

$ordersInstance = new OrderMS();
$orders = $ordersInstance->getAllBeru(false, $period);
foreach ($orders as $order) {
    $msOrders[$order['name']] = $order['_id'];
}

foreach ($compactedBeruOrders as $orderInBeru => $status) {
    if (!isset($msOrders[$orderInBeru])) {
        var_dump($orderInBeru);
        die();
    }
}
// check if order in beru comnfirmed - if not - confirm

