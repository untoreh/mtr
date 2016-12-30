<?php
namespace Mtr;

use GuzzleHttp\Client;

interface Service
{
    function __construct(
        Mtr &$mtr,
        Client &$gz,
        TextReq &$txtrq,
        LanguageCode &$ld
    );

    function translate($source, $target, $input);

    function genReq(array $params);

    function preReq(array &$input);

    function getLangs();
}