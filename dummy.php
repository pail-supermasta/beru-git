<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 17.06.2020
 * Time: 17:57
 */

require_once 'vendor/autoload.php';
require_once 'src/Telegram.php';
$config = require_once 'config.php';

use Avaks\MS\MSSync_dummy;
use Avaks\Beru\Stocks;
use Avaks\Beru\Product;

$collection = (new MSSync_dummy())->MSSync;
$filter = ['_store' => '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'];


try {
    $collection->report_stock_all->find($filter);
} catch (\Exception $e) {
    echo http_response_code(200);
    telegram($e, '-427337827');

}
