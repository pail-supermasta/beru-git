<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.10.2019
 * Time: 17:04
 */

// required headers
header("Access-Control-Allow-Origin: http://localhost/rest-api-authentication-example/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


error_reporting(E_ALL);
ini_set("error_log", "php-error.log");


// get posted data
$jsonBeruPost = file_get_contents("php://input");


function getStock($beruSku)
{
    //query mongodb for avail
    $avail = 0;
    $updatedAt = '2019-09-09T13:01:18+03:00';

    $items = array(array(
        'type' => 'FIT',
        'count' => $avail,
        'updatedAt' => $updatedAt
    ));
    return $items;
}


$jsonBeruPost = json_decode($jsonBeruPost, true);
if (isset($jsonBeruPost['skus'])) {
    $skus = array();

    foreach ($jsonBeruPost['skus'] as $skuValue) {
        $items = getStock($skuValue);
//        echo $items;
        $skuItem = array(
            'sku' => $skuValue,
            'warehouseId' => 2,
            'items' => $items
        );
        $skus[] = $skuItem;
    }
    $skus = json_encode($skus);
}

if (!empty($jsonBeruPost)) {

    http_response_code(200);
    echo $jsonOutput = '{"skus": ' . $skus . '}';

    /*    echo '{
              "{
                "skus": [{
                        "sku": "A200.190",
                        "warehouseId": 2,
                        "items": [{
                            "type": "FIT",
                            "count": 15,
                            "updatedAt": "2019-09-09T13:01:18+03:00"
                        }]
                    },
                    {
                        "sku": "A287.14",
                        "warehouseId": 2,
                        "items": [{
                            "type": "FIT",
                            "count": 7,
                            "updatedAt": "2019-09-09T12:44:08+03:00"
                        }]
                    }
                ]
            }';*/

} else {

    http_response_code(400);

}

error_log('Post-body  ' . $jsonBeruPost);
error_log('Headers ' . json_encode(apache_request_headers()));










