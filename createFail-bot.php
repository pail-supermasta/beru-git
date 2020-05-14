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

$config = require_once '../beru_config/config.php';

use Avaks\Beru\Order;
use Avaks\MS\OrderMS;
use Avaks\Custom\Custom;


//get all orders for 7 days from beru

// get get all orders for 7 days from MS

// match
//if no match Beru->MS create order in MS


