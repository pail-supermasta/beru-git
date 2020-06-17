<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 04.06.2020
 * Time: 13:18
 */


error_reporting(E_ALL);

require_once 'vendor/autoload.php';

$config = require_once '../beru_config/config.php';

use Avaks\MS\OrderMS;
use Avaks\MS\MSSync;


//{_agent: '782c484a-6749-11ea-0a80-03f900263ee6', contract :{$exists : false}}

$ordersMS = new OrderMS();
//Доставляется
$state = '327c03c6-75c5-11e5-7a40-e89700139938';
$ordersBeruShipping = $ordersMS->getAllBeru($state);

$collection = (new MSSync())->MSSync;
//Маркет
$filter = [
    '_agent' => '782c484a-6749-11ea-0a80-03f900263ee6',
    'contract' => ['$exists' => false]
];

$orderCursor = $collection->customerorder->find($filter)->toArray();

foreach ($orderCursor as $order){
    echo $order['name'].PHP_EOL;
    $postdata = '{
            "contract" : {
                "meta" : {
                  "href" : "https://online.moysklad.ru/api/remap/1.1/entity/contract/ff75e854-a182-11ea-0a80-020900039b7d",
                  "type" : "contract",
                  "mediaType" : "application/json"
                }
              }
        }';
    $res = '';
    $res = \Avaks\MS\CurlMoiSklad::curlMS('/entity/customerorder/' . $order['_id'], $postdata, 'put');
    var_dump($res);
    die();
}