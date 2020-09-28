<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 28.09.2020
 * Time: 15:08
 */


$config = require_once '../config_multi.php';

$jsonBeruPost = file_get_contents("php://input");
$beruAuth = $_GET["auth-token"];


if (empty($jsonBeruPost)) {
    error_log('Post-body is empty ' . $jsonBeruPost);
    http_response_code(400);
    die();
}

function validate($config, $beruAuth)
{

    $key = array_search($beruAuth, array_column($config['shop'], 'auth-token'));
    if (!is_bool($key)) {
        return true;
    } else {
        error_log("$beruAuth error");
        http_response_code(403);
        die();
    }
}

validate($config, $beruAuth);