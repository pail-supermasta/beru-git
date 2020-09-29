<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.04.2020
 * Time: 13:44
 */

namespace Avaks\Beru;

use Avaks\Beru\Curl;
use Avaks\Custom\Custom;

class Order
{

    public $id;
    public $items;
    public $shipments;

    function __construct($id = null, $name = null, $positions = null)
    {
        $this->id = $id;
    }

    private function Download($result)
    {
        $path = '/home/beru-service/public_html/files/labels/' . $this->id . '.pdf';
        file_put_contents($path, $result);
    }

    public function getOrder($orgInfo)
    {
        $res = Curl::execute('orders/' . $this->id . '.json',false,false,false,$orgInfo);
        return $res;
    }

    public function getAll($data = false,$orgInfo)
    {
        $variables = '';
        if ($data != false) {
            $variables = "$data";
        }
        $res = Curl::execute("orders.json$variables",false,false,false,$orgInfo);
        return $res;
    }

    public function getSticker($orgInfo)
    {
        $query = 'orders/' . $this->id . '/delivery/labels.json';
        $res = Curl::execute($query,false,false,false,$orgInfo);

        // download
        $this->Download($res);
        return $res;


    }

    public function setDelivery($orderBeru,$orgInfo)
    {

        $this->id = $orderBeru['id'];
        $this->shipments = $orderBeru['delivery']['shipments'][0];
        $boxes = [];
        $itemsQty = sizeof($this->shipments['items']);

        if ($this->shipments['weight'] >= 15 && $itemsQty > 0) {
            foreach ($this->shipments['items'] as $key => $item) {
                $box = [
                    "fulfilmentId" => $this->id . "-" . ($key + 1),
                    "weight" => (int)($this->shipments['weight'] / $itemsQty),
                    "width" => (int)($this->shipments['width'] / $itemsQty),
                    "height" => (int)($this->shipments['height'] / $itemsQty),
                    "depth" => (int)($this->shipments['depth'] / $itemsQty),
                    "items" => array($item)
                ];
                $boxes[] = $box;
            }

            $boxes = json_encode($boxes);

            $postdata = '{"boxes": ' . $boxes . '}';
        } else {
            $postdata = '{
                "boxes": [{
                    "fulfilmentId": "' . $this->id . '-1",
                    "weight": ' . $this->shipments['weight'] . ',
                    "width": ' . $this->shipments['width'] . ',
                    "height": ' . $this->shipments['height'] . ',
                    "depth": ' . $this->shipments['depth'] . ',
                    "items": ' . json_encode($this->shipments['items']) . '
                }]
            }';
        }


        //https://api.partner.market.yandex.ru/v2/campaigns/21621240/orders/17150036/delivery/shipments/4177/boxes
        $query = 'orders/' . $this->id . '/delivery/shipments/' . $this->shipments['id'] . '/boxes.json';
        $res = Curl::execute($query, $postdata, 'put', true,$orgInfo);
        return $res;
    }

    public function setStatus($status, $substatus,$orgInfo)
    {
        $postdata = '{
          "order":
          {
            "status": "' . $status . '",
            "substatus": "' . $substatus . '"
          }
        }';
        //https://api.partner.market.yandex.ru/v2/campaigns/10003/orders/12345/status.json
        $query = 'orders/' . $this->id . '/status.json';
        $res = Curl::execute($query, $postdata, 'put', true,$orgInfo);
        return $res;
    }

    public function setMultipleOrdersStatus($orders, $type = false,$orgInfo)
    {

        if ($type != false && $type == 'canceled') {
            $status = "CANCELLED";
            $substatus = "SHOP_FAILED";

        } elseif ($type != false && $type == 'shipped') {
            $status = "PROCESSING";
            $substatus = "SHIPPED";

        }
        $ordersItems = [];
        foreach ($orders as $order) {
            $orderItem = array(
                "id" => $order,
                "status" => $status,
                "substatus" => $substatus
            );
            $ordersItems[] = $orderItem;
        }
        $ordersItems = json_encode($ordersItems);
        $postdata = '{"orders":' . $ordersItems . '}';
        $query = 'orders/status-update.json';
        $res = Curl::execute($query, $postdata, 'post', true,$orgInfo);
        return $res;
    }
}

