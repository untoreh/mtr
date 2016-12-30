<?php
header('Content-Type: text/plain');
require "../vendor/autoload.php";

use Mtr\Mtr;

$mtr = new Mtr();
$source = 'en';
$target = 'de';
$services = ['google', 'bing', 'yandex'];
$keys = [ 3, 2, 9];

$text = file_get_contents('short.txt');

$results = $mtr->tr($source, $target, [
    $keys[0] => $text,
    $keys[1] => $text,
    $keys[2] => $text
    ], $services);

$c = 0;
foreach ($results as $k => $v) {
    if($keys[$c] !== $k) {
        echo 'Fail';
        return;
    }
    $c++;
}
//print_r($results);
echo 'Success';

