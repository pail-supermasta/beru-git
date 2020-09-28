<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.03.2020
 * Time: 11:19
 */


namespace Avaks\Beru;

use Avaks\MS\MSSync;
use Avaks\Beru\Product;
use Avaks\BackendAPI;


class Stocks
{
    public $available;
    public $updated;
    public $found;
    public $productId;

    public function getMPNFFByIndex()
    {

        $backendAPI = new BackendAPI();

        $data['filter'] = json_encode(array('_id' => $this->productId . '_48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'));

        $data['project'] = json_encode(array(
                '_id' => true,
                '_product' => true,
                'quantity' => true,
                'reserve' => true,
                'stock' => true,
                'updated' => true
            )
        );

        $stockCursor = $backendAPI->getData($backendAPI->urlStock, $data);
        $stockCursor = $stockCursor['rows'][0];

        if (isset($stockCursor['_product'])) {
            $this->found = true;
            $this->available = $stockCursor['stock'] - $stockCursor['reserve'];
            $this->updated = $stockCursor['updated'];
        }

    }

    public function getAll()
    {
        $collection = (new MSSync())->MSSync;
        $filter = ['_store' => '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'];
        $stockCursor = $collection->report_stock_all->find($filter);
        $stockMS = array();
        $available = 0;
        foreach ($stockCursor as $stock) {
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

        return $stockMS;
    }

    public function aggePrBundl()
    {
        $collection = (new MSSync())->MSSync;
        $project = ['$project' =>
            ['_id' => true]
        ];
        $unionWith = ['$unionWith' =>
            [
                'coll' => "bundle",
                'pipeline' => [
                    ['$project' =>
                        ['_id' => true]
                    ]
                ]

            ]
        ];
        $ops = [$project, $unionWith];
        $stockCursor = $collection->product->aggregate($ops);
        foreach ($stockCursor as $product) {
            var_export($product->_id);
            die();
        }
    }
}

require_once '../../vendor/autoload.php';
$aggr = new Stocks();
$aggr->aggePrBundl();
