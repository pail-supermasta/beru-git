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
$config = require_once '../../beru_config/config.php';

use Avaks\MS\OrderMS;
use Avaks\MS\CurlMoiSklad;

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
    if ($config['auth-token'] == $beruAuth) {
        return true;
    } else {
        error_log("$beruAuth error");
        http_response_code(403);
        die();
    }
}

validate($config, $beruAuth);

$newOrder = new OrderMS();

$orderBeru = json_decode($orderBeru, true)['order'];

$newOrder->name = $orderBeru['id'];
$getOrderRes = $newOrder->getByName();


if (isset($getOrderRes['meta'])) {
    http_response_code(200);
    echo $jsonOutput = '{
          "order":
          {
            "accepted": true,
            "id": "' . $orderBeru['id'] . '"
          }
        }';
    $end = microtime(TRUE);
    telegram("POST /order/accept took " . ($end - $start) . " seconds. over.", '-427337827');
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
$preparedOrder = $newOrder->prepareOrder($orderDetails);
$newOrderRes = CurlMoiSklad::curlMS('/entity/customerorder', $preparedOrder, 'post');

if (strpos($newOrderRes, 'обработка-ошибок') > 0 || $newOrderRes == '') {
    http_response_code(500);
    error_log(json_encode($newOrderRes, JSON_UNESCAPED_UNICODE));
    echo 'Ошибка создания заказа в Системе магазина.';
    telegram('ОШИБКА создания заказа ' . $orderBeru['id'], '-427337827');
    die();
} else {
    http_response_code(200);
    echo $jsonOutput = '{
          "order":
          {
            "accepted": true,
            "id": "' . $orderBeru['id'] . '"
          }
        }';
}


$end = microtime(TRUE);
telegram("Заказ " . $orderBeru['id'] . " POST /order/accept took " . ($end - $start) . " seconds.", '-427337827');