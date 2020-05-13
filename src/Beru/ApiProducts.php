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

class ApiProducts
{
    public function getAll()
    {
        $res = Curl::execute("offer-prices.json?limit=2000");
        return $res;
    }

    public function updatePrice($newPrices)
    {


        foreach ($newPrices as $marketSku => $newPrice) {
            $offer = [
                'marketSku' => $marketSku,
                'price' => [
                    'currencyId' => 'RUR',
                    'value' => $newPrice

                ]
            ];
            $offers[] = $offer;
        }
        $postdata['offers'] = $offers;
        $res = Curl::execute("offer-prices/updates.json", json_encode($postdata), 'post');
        return $res;
    }

}

