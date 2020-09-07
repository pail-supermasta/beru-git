<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07.09.2020
 * Time: 10:28
 */

require_once 'Telegram.php';


$headers = array(
    'Content-Type: application/x-www-form-urlencoded'
);


$urlLogin = 'https://api.backendserver.ru/api/v1/auth/login';
$userData = array("username" => "mongodb@техтрэнд", "password" => "!!@th9247t924");

$curl = curl_init($urlLogin);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($userData));

curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($curl, CURLOPT_TIMEOUT_MS, 15000);

$res = curl_exec($curl);
$result = json_decode($res, true);
$all = curl_getinfo($curl);
curl_close($curl);


$url = $all['url'];
$http_code = $all['http_code'];
$userData = array("username" => "***", "password" => "***");
$query = json_encode($userData);


if ($all['total_time'] > 4) {
    require_once 'sql.php';
    $sql = "INSERT INTO logger_1 (id,http_code, url, total_time, created_at,`query`, response) 
            VALUES (DEFAULT,'" . $http_code . "', '" . $url . "', " . $all['total_time'] . ", DEFAULT, '" . $query . "', '" . $res . "')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    telegram($all['total_time'] . " $url", '-420401100');


    $conn->close();
};
