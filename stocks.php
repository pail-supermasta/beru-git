<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.08.2020
 * Time: 16:37
 */

error_reporting(E_ALL);
$start = microtime(TRUE);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


ini_set("error_log", "php-error.log");


require_once 'vendor/autoload.php';
require_once 'src/Telegram.php';
$config = require_once 'config_multi.php';


$jsonBeruPost = file_get_contents("php://input");
$beruAuth = $_GET["auth-token"];


if (empty($jsonBeruPost)) {
    error_log('Post-body is empty ' . $jsonBeruPost);
    http_response_code(400);
    die();
}

function validate($config, $beruAuth)
{

    $key = array_search($beruAuth, array_column($config['shop'], 'auth-token'));
    if (!is_bool($key)) {
        return true;
    } else {
        error_log("$beruAuth error");
        http_response_code(403);
        die();
    }
}

validate($config, $beruAuth);
$urlLogin = 'https://api.backendserver.ru/api/v1/auth/login';
$userData = array("username" => "mongodb@техтрэнд", "password" => "!!@th9247t924");

$urlProduct = 'https://api.backendserver.ru/api/v1/product';
$urlStock = 'https://api.backendserver.ru/api/v1/report_stock_all';


function getToken($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $res = curl_exec($ch);
    $result = json_decode($res, true);
    curl_close($ch);
    return $result['token'];
}

function getData($urlProduct, $data, $token)
{
    $headers = array(
        'Content-Type: application/x-www-form-urlencoded',
        sprintf('Authorization: Bearer %s', $token)
    );

    $data_string = http_build_query($data);

    $ch = curl_init($urlProduct);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_URL, $urlProduct . '/?' . $data_string);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $res = curl_exec($ch);
    $result = json_decode($res, true);
    curl_close($ch);

    return $result;
}

function getQuantity($urlProduct, $token)
{

    $data['limit'] = 999999;
    $data['offset'] = 0;
    $data['project'] = json_encode(array(
            '_id' => true,
            '_product' => true,
            'quantity' => true,
            'reserve' => true,
            'stock' => true,
            'updated' => true
        )
    );

    $data['filter'] = json_encode(array('_store' => '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'));

    $headers = array(
        'Content-Type: application/x-www-form-urlencoded',
        sprintf('Authorization: Bearer %s', $token)
    );

    $data_string = http_build_query($data);
    $ch = curl_init($urlProduct);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_URL, $urlProduct . '/?' . $data_string);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $res = curl_exec($ch);
    $result = json_decode($res, true);
    curl_close($ch);

    return $result;
}



$token = getToken($urlLogin, $userData);

$data['filter'] = json_encode(array('attributes.id' => '032490b9-6d8f-11ea-0a80-027100264b27'));

$data['limit'] = 9999;
$data['offset'] = 0;
$data['project'] = json_encode(array(
        '_id' => true,
        '_attributes.ID_BERU' => true
    )
);

$products = getData($urlProduct, $data, $token);


$stocks = getQuantity($urlStock, $token);
$stockMS = [];

if ($stocks['rows']) {
    foreach ($stocks['rows'] as $k => $stock) {

        if (!isset($stock['reserve'])) {
            $available = $stock['stock'];
        } else {
            $available = $stock['stock'] - $stock['reserve'];
        }

        if ($available <= 0) {
            $available = 0;
        }
        $stockMS[$stock['_product']] = array('available' => $available, 'updated' => $stock['updated']);
    }
}
$jsonBeruPost = json_decode($jsonBeruPost, true);


if (isset($jsonBeruPost['skus']) && isset($jsonBeruPost['warehouseId'])) {
    $skus = array();

    foreach ($jsonBeruPost['skus'] as $skuValue) {

        $skuFound = false;

        foreach ($products['rows'] as $product) {
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
        if ($skuFound == true) continue;


    }
    $skus = json_encode($skus);


    http_response_code(200);
    echo $jsonOutput = '{"skus": ' . $skus . '}';

}

$end = microtime(TRUE);
//telegram("POST /stocks took " . round(($end - $start), 2) . " seconds.", '-427337827');
