<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.03.2020
 * Time: 12:16
 */

namespace Avaks\Beru;

use Avaks\MS\MSSync;
use Avaks\BackendAPI;


class Product
{
    public $id;
    public $price;

    public function findByID_BERU($idBeru)
    {
        $backendAPI = new BackendAPI();
        $data['filter'] = json_encode(array('_attributes.ID_BERU' => "$idBeru"));
        $data['project'] = json_encode(array(
                'name' => true,
                '_attributes' => true
            )
        );

        $productCursor = $backendAPI->getData($backendAPI->urlProduct, $data);
        $productCursor = $productCursor['rows'][0];
        $this->id = $productCursor['_id'] ?? null;
        $this->price = $productCursor['_attributes']['Цена BERU'] ?? 0;
    }


    public function findWithID_BERU()
    {
        $backendAPI = new BackendAPI();
        $data['filter'] = json_encode(array('attributes.id' => '032490b9-6d8f-11ea-0a80-027100264b27'));
        $data['limit'] = 9999;
        $data['offset'] = 0;
        $data['project'] = json_encode(array(
                '_id' => true,
                '_attributes.ID_BERU' => true,
                '_attributes.Цена BERU' => true
            )
        );

        $productsCursor = $backendAPI->getData($backendAPI->urlProduct, $data);
        $productsCursor = $productsCursor['rows'];
        return $productsCursor;
    }
}
