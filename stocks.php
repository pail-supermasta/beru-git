<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.10.2019
 * Time: 17:04
 */

// required headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


error_reporting(E_ALL);
ini_set("error_log", "php-error.log");


require_once 'vendor/autoload.php';
$config = require_once '../beru_config/config.php';

use Avaks\Stocks;

// get posted data
$jsonBeruPost = file_get_contents("php://input");
$beruAuth = $_GET["auth-token"];

function getStock($idBeru)
{
    $stocks = new Stocks();
    $stocks->getMPNFF($idBeru);
    if ($stocks->found == false) return false;
    $items = array(array(
        'type' => 'FIT',
        'count' => $stocks->available,
        'updatedAt' => $stocks->updated
    ));
    return $items;
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

$jsonBeruPost = json_decode($jsonBeruPost, true);
if (isset($jsonBeruPost['skus']) && isset($jsonBeruPost['warehouseId'])) {
    $skus = array();

    foreach ($jsonBeruPost['skus'] as $skuValue) {
        $items = getStock($skuValue);
        if ($items!=false) {
            $skuItem = array(
                'sku' => $skuValue,
                'warehouseId' => $jsonBeruPost['warehouseId'],
                'items' => $items
            );
            $skus[] = $skuItem;
        }

    }
    $skus = json_encode($skus);
}

if (!empty($jsonBeruPost)) {

    http_response_code(200);
    echo $jsonOutput = '{"skus": ' . $skus . '}';

    error_log('Post-body  ' . json_encode($jsonBeruPost));
    error_log('Headers ' . json_encode(apache_request_headers()));
} else {
    error_log('Post-body is empty ' . json_encode($jsonBeruPost));
    http_response_code(400);
    die();
}











