<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.03.2020
 * Time: 11:19
 */


namespace Avaks;

use Avaks\MSSync;
use Avaks\Product;


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
}

/*require_once '../vendor/autoload.php';
$stocks = new Stocks();
$stocks->getMPNFF('A277.14');
echo $stocks->available;*/