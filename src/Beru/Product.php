<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.03.2020
 * Time: 12:16
 */

namespace Avaks\Beru;

use Avaks\MS\MSSync;


class Product
{
    public $id;
    public $price;

    public function findByID_BERU($idBeru)
    {
        $collection = (new MSSync())->MSSync;
        $filter = ['_attributes.ID_BERU' => $idBeru];

        $productCursor = $collection->product->findOne($filter);
        $this->id = $productCursor->_id ?? null;
        $this->price = $productCursor->_attributes['Цена BERU'] ?? 0;
    }

    public function findByBarcode($barcode)
    {
        $collection = (new MSSync())->MSSync;
        $filter = ['code' => $barcode];

        $productCursor = $collection->product->findOne($filter);
        $this->id = $productCursor->_id ?? null;
    }

    public function findWithID_BERU()
    {
        $collection = (new MSSync())->MSSync;
        $filter = ['attributes.id' => '032490b9-6d8f-11ea-0a80-027100264b27'];
        $productCursor = $collection->product->find($filter)->toArray();
        return $productCursor;
    }
}

/*require_once '../../vendor/autoload.php';
$product = new Product();
$product->findByID_BERU('AV71105');*/