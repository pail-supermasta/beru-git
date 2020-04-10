<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07.04.2020
 * Time: 13:11
 */

namespace Avaks\MS;

use Avaks\Beru\Product;

class OrderMS
{

    public $id;
    public $name;
    public $positions;
    public $state;
    public $humanState;

    function __construct($id = null, $name = null, $positions = null)
    {
        $this->id = $id;
        $this->positions = $positions;
        $this->name = $name;
    }

    public function getById()
    {
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id);
        return $res;
    }

    public function getByName()
    {
        $res = CurlMoiSklad::curlMS('/entity/customerorder/?filter=name=' . $this->name);
        return isset((json_decode($res, true))['rows'][0]) ? (json_decode($res, true))['rows'][0] : $res;
    }

    public function fillPosition($item)
    {
        //product
        $product = new Product();
        $product->findByID_BERU($item['offerId']);


        $position = array(
            "quantity" => $item['count'],
            "price" => $item['price']*100,
            "discount" => 0,
            "vat" => 0,
            "assortment" =>
                array(
                    "meta" =>
                        array(
                            "href" => "https://online.moysklad.ru/api/remap/1.1/entity/product/$product->id",
                            "type" => "product",
                            "mediaType" => "application/json"
                        ),
                ),
            "reserve" => $item['count']
        );
        return $position;
    }

    public function prepareOrder($orderDetails)
    {

        /*ждем оплаты*/
        $state = '327c0111-75c5-11e5-7a40-e89700139936';
        $logisticsProvider = '1 Не нужна доставка';

        $postdata = '{
            "name": "' . $orderDetails['order'] . '",
            "moment": "' . $orderDetails['moment'] . '",
            "deliveryPlannedMoment": "' . $orderDetails['deliveryPlannedMoment'] . '",
            "applicable": true,
            "owner": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/employee/f4393347-d106-11e8-9109-f8fc0001112a", 
                    "type": "employee",
                    "mediaType": "application/json"
                }
            },
            "vatEnabled": false,
            "shared": true,
            "organization": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/organization/52af56a1-78d1-11ea-0a80-03db00085f6d",
                    "type": "organization",
                    "mediaType": "application/json"
                }
            },
            "agent": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/counterparty/782c484a-6749-11ea-0a80-03f900263ee6",
                    "type": "counterparty",
                    "mediaType": "application/json"
                }
            },	
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/' . $state . '",
                    "type": "state",
                    "mediaType": "application/json"
                }
            },
            "store": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/store/48de3b8e-8b84-11e9-9ff4-34e8001a4ea1",
                    "metadataHref": "https://online.moysklad.ru/api/remap/1.1/entity/store/metadata",
                    "type": "store",
                    "mediaType": "application/json",
                    "uuidHref": "https://online.moysklad.ru/app/#warehouse/edit?id=48de3b8e-8b84-11e9-9ff4-34e8001a4ea1"
                }
            },
            "attributes": [{
                    "id": "5b766cb9-ef7e-11e6-7a31-d0fd001e5310",
                    "value": "БЕРУ"
                },
                {
                    "id": "547ff930-ef8e-11e6-7a31-d0fd0021d13e",
                    "value": "Адрес"
                },
                {
                    "id": "4552a58b-46a8-11e7-7a34-5acf002eb7ad",
                    "value": {
                        "name": "' . $logisticsProvider . '"
                    }
                },
                {
                    "id": "c4e03fe6-46f3-11e8-9ff4-34e8002246bf",
                    "value": {
                        "name": "--"
                    }
                }
            ],
            "positions": ' . $orderDetails['positions'] . '
        }';
        return $postdata;
    }

    public function setCanceled()
    {
        $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/327c070c-75c5-11e5-7a40-e8970013993b",
                    "type": "state",
                    "mediaType": "application/json"
                }
            }
        }';
        $res = '';
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        $this->state = '327c070c-75c5-11e5-7a40-e8970013993b';
        $this->humanState = 'Отменен';
        return $res;
    }

    public function setInWork()
    {

        $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/ecf45f89-f518-11e6-7a69-9711000ff0c4",
                    "type": "state",
                    "mediaType": "application/json"
                }
            }
        }';
        $res = '';
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        $this->state = 'ecf45f89-f518-11e6-7a69-9711000ff0c4';
        $this->humanState = 'Оплачен';
        return $res;
    }
}