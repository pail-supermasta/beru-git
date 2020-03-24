<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.03.2020
 * Time: 12:16
 */

namespace Avaks;

use Avaks\MSSync;


class Product
{
    public $id;

    public function findByID_BERU($idBeru)
    {
        $collection = (new MSSync())->MSSync;
        $filter = ['attributes.value' => $idBeru];

        $productCursor = $collection->product->findOne($filter);
        $this->id = $productCursor->_id ?? null;
    }
}

/*require_once '../vendor/autoload.php';
$product = new Product();
$product->findByID_BERU('BG010/01/DarkBlue');*/