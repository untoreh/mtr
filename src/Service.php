<?php
namespace Mtr;

use GuzzleHttp\Client;

interface Service
{
    /**
     * Service constructor.
     * @param Mtr $mtr
     * @param Client $gz
     * @param TextReq $txtrq
     * @param LanguageCode $ld
     */
    function __construct(
        Mtr &$mtr,
        Client &$gz,
        TextReq &$txtrq,
        LanguageCode &$ld
    );

    /**
     * @param $source
     * @param $target
     * @param $input
     * @return mixed
     */
    function translate($source, $target, $input);

    /**
     * @param array $params
     * @return mixed
     */
    function genReq(array $params);

    /**
     * @param array $input
     * @return mixed
     */
    function preReq(array &$input);

    /**
     * @return array
     */
    function getLangs();
}