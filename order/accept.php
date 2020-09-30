<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07.04.2020
 * Time: 12:25
 */

//timeout 10 секунд.


/*
 * 2. Когда покупатель завершает оформление заказа, на стороне Беру формируется заказ
 * со статусом "status": "RESERVED" (зарезервирован), а магазину поступает запрос
 * POST /order/accept на принятие заказа. Магазин должен отправить ответ с подтверждением
 * принятия заказа ("accepted": true) или с отказом от заказа ("accepted": false).
 * */

$start = microtime(TRUE);
// required headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

date_default_timezone_set('Europe/Moscow');

require_once '../vendor/autoload.php';
require_once '../src/Telegram.php';
$config = require_once '../config_multi.php';

use Avaks\MS\OrderMS;
use Avaks\MS\CurlMoiSklad;
use Avaks\Custom\Custom;

// get posted data
$orderBeru = file_get_contents("php://input");
$beruAuth = $_GET["auth-token"];


if (empty($orderBeru)) {
    echo 'Post-body is empty ' . $orderBeru;
    http_response_code(400);
    die();
}

function validate($config, $beruAuth)
{

    //from post query
    $key = array_search($beruAuth, array_column($config['shop'], 'auth-token'));
    if (!is_bool($key)) {
        return $config['shop'][$key]['orgInfo'];
    } else {
        error_log("$beruAuth error");
        http_response_code(403);
        die();
    }
}

$orgInfo = validate($config, $beruAuth);

$newOrder = new OrderMS();

$orderBeru = json_decode($orderBeru, true)['order'];

$newOrder->name = $orderBeru['id'];
$getOrderRes = $newOrder->getByName();


if (isset($getOrderRes['id'])) {
    http_response_code(200);
    echo $jsonOutput = '{
          "order":
          {
            "accepted": true,
            "id": "' . $orderBeru['id'] . '"
          }
        }';
    $end = microtime(TRUE);
    telegram("POST /order/accept took " . round(($end - $start), 2) . " seconds. over.", '-427337827');
    die();
} elseif ($orderBeru['id'] == "" || $orderBeru['id'] == null) {
    http_response_code(400);
    echo 'order.id null или пустое значение';
    die();
}

$orderDetails['order'] = $orderBeru['id'];
$orderDetails['moment'] = date("Y-m-d H:i:s");
$deliveryPlannedMoment = strtotime($orderBeru['delivery']['shipments'][0]['shipmentDate']);
$orderDetails['deliveryPlannedMoment'] = date("Y-m-d H:i:s", $deliveryPlannedMoment);


$positions = array();

foreach ($orderBeru['items'] as $item) {
    $position = $newOrder->fillPosition($item);
    array_push($positions, $position);
}


$orderDetails['positions'] = json_encode($positions);
$preparedOrder = $newOrder->prepareOrder($orderDetails,$orgInfo);
$newOrderRes = CurlMoiSklad::curlMS('/entity/customerorder', $preparedOrder, 'post');

$message = 'ОШИБКА создания заказа ' . $orderBeru['id'];
Custom::sendErrorTelegram($newOrderRes, $message, 'accept', true);


// check if order created in MS
$getOrderRes = $newOrder->getByName();


if (isset($getOrderRes['id'])) {
    http_response_code(200);
    echo $jsonOutput = '{
          "order":
          {
            "accepted": true,
            "id": "' . $orderBeru['id'] . '"
          }
        }';

    $newOrderRes = json_decode($newOrderRes, true);
    $orderLinkMS = $newOrderRes['meta']['uuidHref'];

    $orderId = $orderBeru['id'];
    $end = microtime(TRUE);
    telegram("Заказ [$orderId]($orderLinkMS) POST /order/accept took " . round(($end - $start), 2) . " seconds.", '-427337827', 'Markdown');

} else {
    http_response_code(200);
    $message = 'ОШИБКА создания заказа ' . $orderBeru['id'];
    telegram($message, '-427337827', 'Markdown');
}


