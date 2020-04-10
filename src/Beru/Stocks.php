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


class Stocks
{
    public $available;
    public $updated;
    public $found;

    public function getMPNFF($idBeru)
    {

        $this->found = false;
        $collection = (new MSSync())->MSSync;

        $product = new Product();
        $product->findByID_BERU($idBeru);
        $filter = ['_product' => $product->id, '_store' => '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'];

        $stockCursor = $collection->report_stock_all->findOne($filter);
        if (isset($stockCursor->_product)) {
            $this->found = true;
            $this->available = $stockCursor->stock - $stockCursor->reserve;
            $this->updated = $stockCursor->updated;
        }

    }

    public function getMPNFFByBarcode($barcode)
    {

        $this->found = false;
        $collection = (new MSSync())->MSSync;

        $product = new Product();
        $product->findByBarcode($barcode);
        $filter = ['_product' => $product->id, '_store' => '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'];

        $stockCursor = $collection->report_stock_all->findOne($filter);
        if (isset($stockCursor->_product)) {
            $this->found = true;
            $this->available = $stockCursor->stock - $stockCursor->reserve;
            $this->updated = $stockCursor->updated;
        }

    }

    public function getAll()
    {
        $collection = (new MSSync())->MSSync;
        $filter = ['_store' => '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'];
        $stockCursor = $collection->report_stock_all->find($filter);
        $stockMS = array();
        foreach ($stockCursor as $stock) {
            $available = $stock['stock'] - $stock['reserve'];
            if ($available <= 0) {
                $available = 0;
            }
            $stockMS[$stock['_product']] = array('available' => $available, 'updated' => $stock['updated']);
        }

        return $stockMS;
    }
}

/*require_once '../vendor/autoload.php';
$stocks = new Stocks();
$stocks->getMPNFF('A277.14');
echo $stocks->available;*/