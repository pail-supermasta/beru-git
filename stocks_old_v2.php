<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.04.2020
 * Time: 12:20
 */

//timeout 10 секунд.

$start = microtime(TRUE);
// required headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


error_reporting(E_ALL);
ini_set("error_log", "php-error.log");


require_once 'src/Telegram.php';
$config = require_once 'config.php';


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




$jsonBeruPost = json_decode($jsonBeruPost, true);

if (isset($jsonBeruPost['skus']) && isset($jsonBeruPost['warehouseId'])) {
    $skus = array();

    foreach ($jsonBeruPost['skus'] as $skuValue) {
        $temp_stock_json = file_get_contents("temp_stock.json");
        $temp_stock = json_decode($temp_stock_json, true);
        if(isset($temp_stock[$skuValue])){
            $skuStock = $temp_stock[$skuValue];
        } else{
            $skuStock = 0;
        }

        $skuItem = array(
            'sku' => $skuValue,
            'warehouseId' => $jsonBeruPost['warehouseId'],
            'items' => array(array(
                'type' => 'FIT',
                'count' => $skuStock,
                'updatedAt' => date('c'),
            ))
        );
        $skus[] = $skuItem;



    }
    $skus = json_encode($skus);


    http_response_code(200);
    echo $jsonOutput = '{"skus": ' . $skus . '}';

}

$end = microtime(TRUE);
telegram("POST /stocks took " . round(($end - $start), 2) . " seconds.", '-427337827');
