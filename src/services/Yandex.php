<?php
namespace Mtr;

use GuzzleHttp\Client;

/**
 * @property TextReq txtrq
 */
class Yandex
    extends
    Ep
    implements
    Service
{

    public $mtr;
    public $txtrq;

    private $limit = 660;
    private $service = 'yandex';

    public function __construct(
        Mtr &$mtr,
        Client &$gz,
        TextReq &$txtrq,
        LanguageCode &$ld
    ) {
        parent::__construct($mtr, $gz, $txtrq, $ld);

        $this->misc['rgx'] = $txtrq->setRegex('yandex', $this->limit);
        $this->misc['weight'] = 30;
        $this->misc['yandexId'] = apcu_fetch('mtr_yandex_id');
        $this->urls['yandexL'] =
            'https://translate.yandex.net/api/v1/tr.json/getLangs';
        $this->urls['yandex1'] = 'https://translate.yandex.com';
        $this->urls['yandex2'] =
            'https://translate.yandex.net/api/v1/tr.json/translate';
        $this->params['yandex'] = [
            'headers' => [
                'Host' => 'translate.yandex.net',
                'Accept' => '*/*',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Referer' => 'https=>//translate.yandex.com/',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Origin' => 'https=>//translate.yandex.com',
                'Connection' => 'keep-alive'
            ],
            'query' => ['srv' => 'tr-text', 'reason' => 'paste']
        ];
    }

    function translate($source, $target, $input)
    {
        $this->preReq($input);

        $this->params['yandex']['query'] =
            array_merge($this->params['yandex']['query'], [
                'lang' => $source . '-' . $target,
                'id' => $this->misc['yandexId']
            ]);
        list($inputs, $str_ar) = $this->genQ($input, 'genReq');
        $res =
            $this->reqResponse('POST', 'yandex2', $this->params['yandex'], $inputs);

        foreach ($res as $re) {
            $translation[] = json_decode($re, true)['text'][0];
        }
        $translated = $this->joinTranslated($str_ar, $input, $translation, $this->mtr->splitGlue);

        return $translated;
    }

    function genReq(array $params)
    {
        return ['form_params' => ['text' => $params['data']], 'options' => '4'];
    }

    function preReq(&$input)
    {
        if (!$this->misc['yandexId']) {
            // $res = file_get_contents('yandex.cookies');
            // preg_match('/SID: \'.*/', $res, $matches);
            preg_match('/SID: \'.*/', $this->reqResponse('GET', 'yandex1'),
                $matches);
            preg_match('/(?<=SID: \').*(?=\')/', $matches[0], $SID);
            foreach (explode('.', $SID[0]) as $key => $s) {
                $sidRev[$key] = strrev($s);
            }
            if (!empty($sidRev)) {
                $this->misc['yandexId'] = implode('.', $sidRev) . '-0-0';
                apcu_store('mtr_yandex_id', $this->misc['yandexId'], $this->ttl());
            } else {
                throw new \Exception('Yandex preparation failed.');
            }
        }

        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue'], $this->limit, $this->service);
    }

    function getLangs()
    {
        $this->params['yandex']['query']['ui'] = 'en';
        $res = $this->reqResponse('GET', 'yandexL', $this->params['yandex']);

        return array_keys(json_decode($res, true)['langs']);
    }

}
