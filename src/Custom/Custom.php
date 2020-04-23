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
            error_log(json_encode($res, JSON_UNESCAPED_UNICODE), 3, $step . ".log");
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
            error_log($res, 3, $step . ".log");
            telegram($message, '-427337827');
            return true;
        }
        return false;
    }
}