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

$config = require_once '../beru_config/config.php';

date_default_timezone_set('Europe/Moscow');

use Avaks\Beru\Reception;

$reception = new Reception();
$reception->getAct();
$receptionLink = 'https://beru.ltplk.ru/files/receptions/reception_transfer_act_21621240_' . date('d-m-yy') . '.pdf';
$date = date('d-m-yy');

telegram("Акт приема передачи отправлений BERU [$date]($receptionLink)", '-385044014', 'Markdown');


