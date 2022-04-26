<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07.04.2022
 * Time: 14:10
 */

$oldDescr = "Предоставлена скидка типа LOY (Скидка БР (с ограничением МВМ)) от goods в размере 729 руб Goods.ru: Заказ подтвержден Goods.ru: Заказ поставлен в отгрузку Goods.ru: Заказ доставлен Cost payments: 22.35 Cost payments: 22.35  Cost payments: 22.35";
$oldDescrNL = "Предоставлена скидка типа undefined ((SberMassPers все каналы) Промокод SBDZP3% 500 от) от goods в размере 500 руб \r\nЗаказ отменен по инициативе покупателя Goods.ru \r\nCost payments: \r\nCost payments:  \r\nCost payments:";
$response = trim(preg_replace('/Cost payments: \d+(\.\d+)/', "", $oldDescr));
$response.="\r\nCost payments: 22.35";
var_dump($response);
die();