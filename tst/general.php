<?php
header('Content-Type: text/plain');
require "../vendor/autoload.php";

use Mtr\Mtr;

$options = [
    'systran' => ['key' => 'bumPX7NIxqmshawMILIXKJqBGTUjp1pVQu7jsn5MsDhsPyCku1']
];
$mtr = new Mtr($options);
$source = 'en';
$target = 'de';
$services = ['google', 'bing', 'yandex'];
$keys = [3, 2, 9];

$text = file_get_contents('large.txt');
$text2 = file_get_contents('short.txt');
$text3 = file_get_contents('medium.txt');

$results = $mtr->tr($source, $target, [
    $keys[0] => $text,
    $keys[1] => $text2,
    $keys[2] => $text3
], $services);

foreach ($results as $key => $v) {
    if (empty($v)) {
        echo 'Fail';

        return;
    }
}
//print_r($results);
echo 'Success';

