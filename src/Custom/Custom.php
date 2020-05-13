<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 22.04.2020
 * Time: 17:34
 */

namespace Avaks\Custom;

class Custom
{
    public static function sendErrorTelegram($res, $message, $step, $die = false, $continue = false)
    {
        if (strpos($res, 'обработка-ошибок') > 0 || $res == '' || $res == false) {
            http_response_code(500);
            error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . " " . json_encode($res, JSON_UNESCAPED_UNICODE) . PHP_EOL, 3, $step . ".log");
            telegram($message, '-427337827');
            if ($continue != false) {
                return true;
            } else if ($die != false) {
                die();
            }
        }
        return false;
    }

    public static function sendErrorTelegramBeru($res, $message, $step)
    {
        if (strpos($res, 'ERROR') > 0 || strpos($res, 'error') > 0 || $res == '') {
            error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . " " . $res . PHP_EOL, 3, $step . ".log");
            telegram($message, '-427337827');
            return true;
        }
        return false;
    }
}