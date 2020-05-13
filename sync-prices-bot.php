<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.05.2020
 * Time: 16:13
 */


error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

require_once 'vendor/autoload.php';
require_once 'src/Telegram.php';
$config = require_once '../beru_config/config.php';

use Avaks\Beru\Product;
use Avaks\Beru\ApiProducts;
use Avaks\Custom\Custom;

//get all beru products MS
$product = new Product();
$productCursor = $product->findWithID_BERU();
$beruProductsMS = [];
foreach ($productCursor as $product) {
    $beruProductsMS[$product['_attributes']['ID_BERU']] = $product['_attributes']['Цена BERU'] ?? null;
}

// get all products from BERU
$apiProducts = new ApiProducts();
$apiProductsRes = $apiProducts->getAll();
$offers = json_decode($apiProductsRes, true)['result']['offers'];
$beruProductsApi = [];
foreach ($offers as $offer) {
    $beruProductsApi[$offer['shopSku']] = [
        'price' => $offer['price']['value'],
        'marketSku' => $offer['marketSku']
    ];
}
$newPrices = [];
$count = 0;
foreach ($beruProductsMS as $beruProductMSName => $beruProductMSPrice) {

    if (isset($beruProductsApi[$beruProductMSName]['price'])
        && $beruProductsApi[$beruProductMSName]['price'] != $beruProductMSPrice
        && $beruProductMSPrice != null) {
        $newPrices[$beruProductsApi[$beruProductMSName]['marketSku']] = $beruProductMSPrice;
        $count++;
    }
}
if ($count > 0) {
    $res = $apiProducts->updatePrice($newPrices);
    $message = 'ОШИБКА updatePrice';
    $continue = Custom::sendErrorTelegramBeru($res, $message, 'updatePrice');
    if (!$continue) {
        telegram("Обновлено $count товаров", '-427337827');
    }
}

