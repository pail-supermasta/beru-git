<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.08.2020
 * Time: 11:46
 */

namespace Avaks\Beru;


class Reception
{
    private function Download($result,$shopName)
    {
//        $path = 'files/receptions/reception_transfer_act_21621240_' . date('d-m-yy') . '.pdf';
        $path = '/home/beru-service/public_html/files/receptions/reception_transfer_act_'.$shopName.'_' . date('d-m-yy') . '.pdf';
        file_put_contents($path, $result);
    }

    public function getAct($orgInfo,$shopName)
    {
        $query = '/shipments/reception-transfer-act.json';
        $res = Curl::execute($query,false,false,false,$orgInfo);

        // download
        $this->Download($res,$shopName);
        return $res;


    }
}