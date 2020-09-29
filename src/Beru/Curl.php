<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.08.2019
 * Time: 13:07
 */

namespace Avaks\Beru;


class Curl
{

    /**
     * @param $link
     * @param $token
     * @param $data
     * @param bool $display
     * @return mixed
     */


    public static function execute($link, $data = false, $type = false, $display = false, $orgInfo = false)
    {

        $oauth_token = 'AgAAAAA-LlaIAAY9-pTT9l04e08ZtW-TMVb4nwc';//Маркет fallback
        $oauth_client_id = '3e40dc89bdff413f81d5a8a8f23109a0';

        if (isset($orgInfo['oauth_token'])) $oauth_token = $orgInfo['oauth_token'];
        if (isset($orgInfo['oauth_client_id'])) $oauth_client_id = $orgInfo['oauth_client_id'];

        $headers = array(
            0 => "Content-Type: application/json",
            1 => 'Authorization: OAuth oauth_token="'.$oauth_token.'", oauth_client_id="'.$oauth_client_id.'"'
        );


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_URL, 'https://api.partner.market.yandex.ru/v2/campaigns/21621240/' . $link);


        if ($type == 'put') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        } elseif ($type == 'post') {
        } else {
            curl_setopt($curl, CURLOPT_HTTPGET, true);
        }


        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            if ($display == true) {
                echo "Post body is: \n" . $data . "\n";
            }

        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);


        $result = curl_exec($curl);
        $info = curl_getinfo($curl);

        if ($display == true) {
            print_r("\n" . $info['request_header']);
        }



        $curl_errno = curl_errno($curl);
        curl_close($curl);

        if ($curl_errno == 0) {
            return $result;
        } else {
            return $curl_errno;
        }

    }

}