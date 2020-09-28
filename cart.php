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

/*$jsonBeruPost = '{
    "cart": {
        "currency": "RUR",
        "items": [
            {
                "feedId": 737072,
                "offerId": "PROF",
                "offerName": "Набор пластика UNID для 3D ручки UNID PRO-F (по 10м. 3 цвета свеиящихся в темноте, в коробке)",
                "subsidy": 0,
                "count": 5,
                "params": "Вес: 0.08 кг",
                "fulfilmentShopId": 618886,
                "sku": "100929014846",
                "shopSku": "PROF"
            },
            {
                "feedId": 737072,
                "offerId": "305003",
                "offerName": "Утюг Morphy Richards 305003 голубой",
                "subsidy": 0,
                "count": 55,
                "params": "Цвет товара: голубой",
                "fulfilmentShopId": 618886,
                "sku": "100825129814",
                "shopSku": "305003"
            }
        ],
        "delivery": {
            "region": {
                "id": 39,
                "name": "Ростов-на-Дону",
                "type": "CITY",
                "parent": {
                    "id": 121146,
                    "name": "Городской округ Ростов-на-Дону",
                    "type": "SUBJECT_FEDERATION_DISTRICT",
                    "parent": {
                        "id": 11029,
                        "name": "Ростовская область",
                        "type": "SUBJECT_FEDERATION",
                        "parent": {
                            "id": 26,
                            "name": "Южный федеральный округ",
                            "type": "COUNTRY_DISTRICT",
                            "parent": {
                                "id": 225,
                                "name": "Россия",
                                "type": "COUNTRY"
                            }
                        }
                    }
                }
            }
        }
    }
}';*/

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

