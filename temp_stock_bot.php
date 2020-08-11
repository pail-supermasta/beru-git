<?php

error_reporting(E_ALL);
ini_set("error_log", "temp_stock_bot.log");

require_once 'vendor/autoload.php';
$config = require_once 'config.php';

use Avaks\MS\MSSync;
use Avaks\Beru\Stocks;
use Avaks\Beru\Product;

$collection = (new MSSync())->MSSync;

//report_stock_all
$stocks = new Stocks();
//$stockMS = $stocks->getAll();
//product
$product = new Product();
$productCursor = $product->findWithID_BERU();

$skus = array();
foreach ($productCursor as $product) {
    $product_id = null;
    $product_id = $product['_id'];

    $stocks->productId = $product_id;
    $stocks->available=0;
    $stocks->getMPNFFByIndex();

    if ($stocks->available<0) $stocks->available = 0;
    $skus[$product['_attributes']['ID_BERU']] = $stocks->available;
}
$fp = fopen('temp_stock.json', 'w');
fwrite($fp, json_encode($skus,JSON_UNESCAPED_UNICODE));
fclose($fp);
/*$string = file_get_contents("temp_stock.json");
$json_a = json_decode($string, true);
var_dump($json_a['NPL-06-ZOO']);*/

