<?php
namespace Mtr;

use GuzzleHttp\Client;

class Promt
    extends
    Ep
    implements
    Service
{

    public function __construct(
        Mtr &$mtr,
        Client &$gz,
        TextReq &$txtrq,
        LanguageCode &$ld
    ) {
        parent::__construct($mtr, $gz, $txtrq, $ld);

        $this->misc['weight'] = 10;
        $this->urls['promt'] =
            'http://www.online-translator.com/services/TranslationService.asmx/GetTranslateNew';
        $this->urls['promtL'] = 'http://www.online-translator.com/';
        $this->cookies['promt'] = apc_fetch('mtr_cookies_promt');
        $this->params['promt'] = [
            'headers' => [
                'Host' => 'www.online-translator.com',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Referer' => 'http://www.online-translator.com/',
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Requested-With' => 'XMLHttpRequest',
                'Connection' => 'keep-alive',
            ],
            'cookies' => &$this->cookies['promt'],
            'json' => [
                'template' => 'auto',
                'lang' => 'en',
                'limit' => 3000,
                'useAutoDetect' => true,
                'key' => '',
                'ts' => 'MainSite',
                'tid' => '',
                'IsMobile' => false
            ]
        ];
    }

    function translate($source, $target, $input)
    {
        if ($this->mtr->arr) {
            $this->preReq($input);
        } else {
            return false;
        }

        $this->params['promt']['json'] =
            array_merge($this->params['promt']['json'],
                ['dirCode' => $source . '-' . $target]);

        list($inputs, $str_ar) = $this->genQ($input, 'genReq');
        $res = $this->reqResponse('POST', 'promt', $this->params['promt'], $inputs);
        foreach ($res as $re) {
            $translation[] = str_replace("<br/>", "\n", json_decode($re)->d->result);
        }
        $translated = $this->joinTranslated($str_ar, $input, $translation, $this->misc['splitGlue']);

        return $translated;
    }

    function genReq(array $params)
    {
        return ['json' => ['text' => $params['data']]];
    }

    function preReq(array &$input)
    {
        $this->genC('promt');
        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue']);
    }

    function getLangs()
    {
        preg_match('/LangReserv[\s\S]*?\/select>/',
            $this->reqResponse('GET', 'promtL'), $matches);
        preg_match_all('/value="?([a-zA-Z\-]{2,})"?/', $matches[0], $matches);

        return $matches[1];
    }

}
