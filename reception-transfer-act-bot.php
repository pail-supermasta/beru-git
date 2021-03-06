<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.08.2020
 * Time: 11:43
 */

error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

require_once 'vendor/autoload.php';
require_once 'src/Telegram.php';

//$config = require_once 'config.php';
$config = require_once 'config_multi.php';


date_default_timezone_set('Europe/Moscow');

use Avaks\Beru\Reception;

foreach ($config['shop'] as $shop) {


    $reception = new Reception();
    $reception->getAct($shop['orgInfo'],$shop['name']);
    $receptionLink = 'https://beru-service.a3w.ru/files/receptions/reception_transfer_act_'.$shop['name'].'_' . date('d-m-yy') . '.pdf';
    $date = date('d-m-yy');

    telegram("Акт приема передачи отправлений BERU ".$shop['name']." [$date]($receptionLink)", '-385044014', 'Markdown');

}
