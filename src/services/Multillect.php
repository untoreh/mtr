<?php
namespace Mtr;

use GuzzleHttp\Client;

/**
 * @property TextReq txtrq
 */
class Multillect
    extends
    Ep
    implements
    Service
{

    public $mtr;
    public $txtrq;

    public function __construct(
        Mtr &$mtr,
        Client &$gz,
        TextReq &$txtrq,
        LanguageCode &$ld
    ) {
        parent::__construct($mtr, $gz, $txtrq, $ld);

        $this->misc['weight'] = 10;
        $this->urls['multillectL'] = 'https://translate.multillect.com';
        $this->urls['multillect'] = 'https://translate.multillect.com/form.json';
        $this->cookies['multillect'] = apcu_fetch('mtr_cookies_multillect');
        $this->params['multillect'] = [
            'headers' => [
                'Host' => 'translate.multillect.com',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Referer' => 'https://translate.multillect.com/',
                'x-requested-with' => 'XMLHttpRequest',
                'Connection' => 'keep-alive'
            ],
            'cookies' => &$this->cookies['multillect']
        ];
    }

    function translate($source, $target, $input)
    {
        if ($this->mtr->arr) {
            $this->preReq($input);
        } else {
            return false;
        }

        $this->params['multillect']['query'] = ['from' => $source, 'to' => $target];

        list($inputs, $str_ar) = $this->genQ($input, 'genReq');
        $res =
            $this->reqResponse('GET', 'multillect', $this->params['multillect'],
                $inputs);
        foreach ($res as $re) {
            $translation[] =
                str_replace('<br />', '', html_entity_decode(json_decode($re,
                    true)['result']['translations']));
        }
        $translated =
            $this->joinTranslated($str_ar, $input, $translation,
                $this->misc['splitGlue']);

        return $translated;
    }

    function genReq(array $params)
    {
        return ['query' => ['text' => $params['data']]];
    }

    function preReq(array &$input)
    {
        $this->genC('multillect');
        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue']);
    }

    function getLangs()
    {
        preg_match_all('/data-select-params="(.{2,3})"/',
            $this->reqResponse('GET', 'multillectL'), $matches);

        return array_values(array_unique($matches[1]));
    }

}
