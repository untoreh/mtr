<?php
namespace Mtr;

use GuzzleHttp\Client;

/**
 * @property TextReq txtrq
 */
class Frengly
    extends
    Ep
    implements
    Service
{

    public $mtr;
    public $txtrq;

    private $service = 'frengly';
    private $limit = 1000;

    public function __construct(
        Mtr &$mtr,
        Client &$gz,
        TextReq &$txtrq,
        LanguageCode &$ld
    ) {
        parent::__construct($mtr, $gz, $txtrq, $ld);

        $this->txtrq->setRegex($this->service, $this->limit);
        $this->misc['weight'] = 10;
        $this->urls['frenglyL'] = 'http://www.frengly.com/';
        $this->urls['frenglyL2'] =
            'http://www.frengly.com/frengly/static/langs.json';
        $this->urls['frengly'] = 'http://www.frengly.com/frengly/data/translate/';
        $this->cookies['frengly'] = apcu_fetch('mtr_cookies_frengly');
        $this->params['frengly'] = [
            'headers' => [
                'Host' => 'www.frengly.com',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Referer' => 'http://www.frengly.com/translate',
                'Content-Type' => 'application/json;charset=utf-8',
                'x-requested-with' => 'XMLHttpRequest',
                'Connection' => 'keep-alive'
            ],
            'cookies' => &$this->cookies['frengly']
        ];
    }

    function translate($source, $target, $input)
    {
        $this->preReq($input);

        $this->params['frengly']['json'] =
            [
                'email' => 'ui@frengly.com',
                'password' => 'PlsDontBanMe',
                'src' => $source,
                'dest' => $target
            ];

        list($inputs, $str_ar) = $this->genQ($input, 'genReq');
        $res =
            $this->reqResponse('POST', 'frengly', $this->params['frengly'], $inputs);

        foreach ($res as $re) {
            $mw = '';
            foreach (json_decode($re, true)['list'] as $w) {
                $mw .= $w['destWord'];
            }
            $translation[] = $mw;
        }

        $translated = $this->joinTranslated($str_ar, $input, $translation, $this->misc['splitGlue']);

        return $translated;
    }

    function genReq(array $params)
    {
        return [
            'json' => [
                'text' => $params['data']
            ]
        ];
    }

    function preReq(&$input)
    {
        $this->genC('frengly');
        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue'], $this->limit, $this->service);
    }

    function getLangs()
    {
        return array_keys((array)json_decode($this->reqResponse('GET', 'frenglyL2'))->list);
    }

}
