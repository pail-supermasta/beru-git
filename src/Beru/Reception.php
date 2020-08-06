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
    private function Download($result)
    {
        $path = 'files/receptions/reception_transfer_act_21621240_' . date('d-m-yy') . '.pdf';
        file_put_contents($path, $result);
    }

    public function getAct()
    {
        $query = '/shipments/reception-transfer-act.json';
        $res = Curl::execute($query);

        // download
        $this->Download($res);
        return $res;


    }
}