<?php
header('Content-Type: text/plain');
require "../vendor/autoload.php";

use Mtr\Mtr;

$mtr = new Mtr();
$source = 'en';
$target = 'de';
$services = 'bing';
$keys = [ 3, 2, 9];

$text = file_get_contents('short.txt');

$results = $mtr->tr($source, $target, [
    $keys[0] => $text,
    $keys[1] => $text,
    $keys[2] => $text
    ], $services);

foreach ($results as $key => $v) {
    if(empty($v)) {
        echo 'Fail';
        return;
    }
}
//print_r($results);
echo 'Success';

