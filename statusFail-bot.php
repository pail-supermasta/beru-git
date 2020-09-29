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

//$config = require_once 'config.php';
$config = require_once 'config_multi.php';

use Avaks\Beru\Order;
use Avaks\MS\OrderMS;
use Avaks\Custom\Custom;

//ГОТОВО

foreach ($config['shop'] as $shop) {
    $ordersMS = new OrderMS();


// check Ждем оплаты
    $state = '327c0111-75c5-11e5-7a40-e89700139936';
    $ordersMS->_organization = $shop['orgInfo']['organization'] ?? false;

    $ordersWaitPayment = $ordersMS->getAllBeru($state);
    if (sizeof($ordersWaitPayment) > 0) {
        foreach ($ordersWaitPayment as $orderWaitPayment) {
            $ordersMS->id = $orderWaitPayment['_id'];
            $ordersMS->name = $orderWaitPayment['name'];

            $orderBeru = new Order($orderWaitPayment['name']);
            $response = $orderBeru->getOrder($shop['orgInfo']);
            $orderBeruDetails = json_decode($response, true)['order'];
            if ($orderBeruDetails['status'] == 'CANCELLED') {
                $details = "status: CANCELLED, substatus: " . $orderBeruDetails['substatus'];
                $resSetCanceled = $ordersMS->setCanceled($details);

                $message = 'ОШИБКА обновления статуса CANCELLED заказа ' . $ordersMS->name;
                Custom::sendErrorTelegram($resSetCanceled, $message, 'statusFail-bot', true);

            } elseif ($orderBeruDetails['status'] == 'PROCESSING' && $orderBeruDetails['substatus'] == 'STARTED') {
                $details = "paymentType: " . $orderBeruDetails['paymentType'] . " paymentMethod: " . $orderBeruDetails['paymentMethod'];
                $resSetInWork = $ordersMS->setInWork($details);

                $message = 'ОШИБКА обновления статуса STARTED заказа ' . $ordersMS->name;
                Custom::sendErrorTelegram($resSetInWork, $message, 'status', true);
            }

        }
    }

}

