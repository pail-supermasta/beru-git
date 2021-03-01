<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07.04.2020
 * Time: 10:52
 */

//timeout 5.5 секунд.

/*
 *1. Когда покупатель начинает оформление заказа, магазину поступает запрос POST /cart с целью
 * актуализации данных по цене, наличию товара и т. п. Ответ магазина должен содержать
 * актуальные данные.
 * */

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
$config = require_once 'config_multi.php';

use Avaks\MS\MSSync;
use Avaks\Beru\Stocks;
use Avaks\Beru\Product;

// get posted data
$jsonBeruPost = file_get_contents("php://input");
$beruAuth = $_GET["auth-token"];

if (empty($jsonBeruPost)) {
    echo 'Post-body is empty ' . $jsonBeruPost;
    http_response_code(400);
    die();
}

function validate($config, $beruAuth)
{

    //from post query
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

$stocks = new Stocks();
$product = new Product();



$cart = json_decode($jsonBeruPost, true);
$cartItems = $cart['cart']['items'];


foreach ($cartItems as $cartItem) {
    $productSKU = $cartItem['offerId'];

    $product->findByID_BERU($productSKU);

    $product_id = null;


    $stocks->productId = $product->id;
    $stocks->getMPNFFByIndex();

    if ($stocks->available >= $cartItem['count']) {
        $count = $cartItem['count'];
    } elseif ($stocks->available < $cartItem['count'] && $stocks->available > 0) {
        $count = $stocks->available;
    } else {
        $count = 0;
    }


    $item = array(
        'feedId' => $cartItem['feedId'],
        'offerId' => "" . $cartItem['offerId'] . "",
        'count' => $count
    );
    $items[] = $item;


}
$response = array('cart' => array('items' => $items));


http_response_code(200);
echo json_encode($response);

$end = microtime(TRUE);
//telegram("POST /cart took " .  round(($end - $start), 2) . " seconds.", '-427337827');

