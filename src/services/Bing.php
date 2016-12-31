<?php
namespace Mtr;

use GuzzleHttp\Client;

class Bing
    extends
    Ep
    implements
    Service
{

    function __construct(
        Mtr &$mtr,
        Client &$gz,
        TextReq &$txtrq,
        LanguageCode &$ld
    ) {
        parent::__construct($mtr, $gz, $txtrq, $ld);

        $this->misc['weight'] = 30;
        $this->misc['glue'] = '; ¶; ';
        $this->misc['splitGlue'] = "/;\s?¶;\s?/";
        $this->urls['bingL'] = 'http://www.bing.com/translator/';
        $this->urls['bing'] =
            'http://www.bing.com/translator/api/Translate/TranslateArray';
        $this->cookies['bing'] = apc_fetch('mtr_cookies_bing');
        $this->params['bing'] = [
            'headers' => [
                'Host' => 'www.bing.com',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Referer' => 'https://www.bing.com/translator/',
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Requested-With' => 'XMLHttpRequest',
            ],
            'cookies' => &$this->cookies['bing']
        ];
    }

    function translate($source, $target, $input)
    {
        if ($this->mtr->arr) {
            $this->preReq($input);
        } else {
            return false;
        }
        if ($source == 'auto') {
            $source = '-';
        }

        $this->params['bing'] = array_merge_recursive($this->params['bing'], [
            'query' => ['from' => $source, 'to' => $target]
        ]);

        list($inputs, $str_ar) = $this->genQ($input, 'genReq');

        $res = $this->reqResponse('POST', 'bing', $this->params['bing'], $inputs);
        foreach ($res as $re) {
            $translation[] = json_decode($re, true)['items'][0]['text'];
        }
        $translated = $this->joinTranslated($str_ar, $input, $translation, $this->misc['splitGlue']);

        return $translated;
    }

    function genReq(array $params)
    {
        return [
            'json' => [['text' => $params['data']]]

        ];
    }

    function preReq(array &$input)
    {
        $this->genC('bing');
        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue']);
    }

    function getLangs()
    {
        preg_match_all('/value="?([a-z]{2,3}(\-[A-Z]{2,4})?)"?>/',
            $this->reqResponse('GET', 'bingL'), $matches);

        return array_unique($matches[1]);
    }


}
