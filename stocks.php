<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.04.2020
 * Time: 12:20
 */

//timeout 5.5 секунд.

$start = microtime(TRUE);
// required headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


error_reporting(E_ALL);
ini_set("error_log", "php-error.log");


require_once 'vendor/autoload.php';
require_once 'src/Telegram.php';
$config = require_once '../beru_config/config.php';

use Avaks\MS\MSSync;
use Avaks\Beru\Stocks;
use Avaks\Beru\Product;

// get posted data
$jsonBeruPost = file_get_contents("php://input");
$beruAuth = $_GET["auth-token"];


if (empty($jsonBeruPost)) {
    error_log('Post-body is empty ' . $orderBeru);
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


$collection = (new MSSync())->MSSync;

//report_stock_all
$stocks = new Stocks();
$stockMS = $stocks->getAll();

//product
$product= new Product();
$productCursor= $product->findWithID_BERU();


$jsonBeruPost = json_decode($jsonBeruPost, true);

if (isset($jsonBeruPost['skus']) && isset($jsonBeruPost['warehouseId'])) {
    $skus = array();

    foreach ($jsonBeruPost['skus'] as $skuValue) {

        $skuFound=false;

        foreach ($productCursor as $product) {
            $product_id = null;

            if ($product['_attributes']['ID_BERU'] == $skuValue) {
                $product_id = $product['_id'];

                $skuItem = array(
                    'sku' => $skuValue,
                    'warehouseId' => $jsonBeruPost['warehouseId'],
                    'items' => array(array(
                        'type' => 'FIT',
                        'count' => $stockMS["$product_id"]['available'],
                        'updatedAt' => $stockMS["$product_id"]['updated'],
                    ))
                );
                $skus[] = $skuItem;
                $skuFound = true;
                break;
            }
        }
        if($skuFound == true) continue;


    }
    $skus = json_encode($skus);


    http_response_code(200);
    echo $jsonOutput = '{"skus": ' . $skus . '}';

}

$end = microtime(TRUE);
telegram("POST /stocks took " . ($end - $start) . " seconds.", '-427337827');
