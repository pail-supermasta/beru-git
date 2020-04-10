<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08.04.2020
 * Time: 13:14
 */

//timeout 10 секунд.

/*
 * 1. Создание заказа

Когда покупатель завершает оформление заказа, на стороне Беру формируется заказ со
статусом "status": "RESERVED" (зарезервирован), а магазину поступает запрос POST /order
/accept на принятие заказа.

МС статус заказа - Ждем оплаты

2. Оплата

2.1. Оплата при оформлении

1) Беру отправляет магазину запрос POST /order/status со статусом заказа "status":
"UNPAID" (не оплачен). У покупателя есть два часа, чтобы оплатить заказ.

МС статус заказа - Ждем оплаты

2) После оплаты Беру отправляет магазину запрос POST /order/status со статусом заказа
"status": "PROCESSING" (обрабатывается) и этапом обработки "substatus": "STARTED" (передан
в обработку) — магазин может обрабатывать заказ.

МС статус заказа - В работе

3) Если заказ не оплачен в течение двух часов, Беру отправляет магазину запрос
 POST /order/status со статусом заказа "status": "CANCELLED" (отменен).

МС статус заказа - Отменен


2.2. Оплата при получении

1) Беру отправляет магазину запрос POST /order/status со статусом заказа "status": "PROCESSING"
(обрабатывается) и этапом обработки "substatus": "STARTED" (передан в обработку) — магазин может
обрабатывать заказ.

МС статус заказа - В работе
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


$existingOrder = new OrderMS();

$orderBeru = json_decode($orderBeru, true)['order'];
$existingOrder->name = $orderBeru['id'];
$getOrderRes = $existingOrder->getByName();


if (isset($getOrderRes['meta'])) {
    $existingOrder->id = $getOrderRes['id'];
    if ($orderBeru['status'] == 'PROCESSING' && $orderBeru['substatus'] == 'STARTED') {
        $resSetInWork = $existingOrder->setInWork();
        if (strpos($resSetInWork, 'обработка-ошибок') > 0 || $resSetInWork == '') {
            http_response_code(500);
            error_log(json_encode($resSetInWork, JSON_UNESCAPED_UNICODE));
            echo 'Ошибка обновления статуса заказа в Системе магазина.';
            telegram('ОШИБКА обновления статуса заказа ' . $orderBeru['id'], '-427337827');
            die();
        }
    } elseif ($orderBeru['status'] == 'CANCELLED') {
        $resSetCanceled = $existingOrder->setCanceled();
        if (strpos($resSetCanceled, 'обработка-ошибок') > 0 || $resSetCanceled == '') {
            http_response_code(500);
            error_log(json_encode($resSetCanceled, JSON_UNESCAPED_UNICODE));
            echo 'Ошибка обновления статуса заказа в Системе магазина.';
            telegram('ОШИБКА обновления статуса заказа ' . $orderBeru['id'], '-427337827');
            die();
        }
    } else {
        http_response_code(400);
        echo 'Статус не распознан.';
        die();
    }

} else {
    http_response_code(400);
    echo 'Заказ не найден.';
    die();
}

$end = microtime(TRUE);
telegram("Заказ $existingOrder->name $existingOrder->humanState", '-427337827');
telegram("POST /order/status took " . ($end - $start) . " seconds.", '-427337827');