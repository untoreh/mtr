<?php
namespace Mtr;

use GuzzleHttp\Client;

class Convey
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
        $this->urls['convey'] = 'http://ackuna.com/pages/ajax_translate';
        $this->urls['conveyL'] = 'http://translation.conveythis.com';
        $this->urls['conveyL2'] =
            'http://ackuna.com/pages/ajax_translator_languages/google'; // can also be yandex or bing
        $this->cookies['convey'] = apcu_fetch('mtr_cookies_convey');
        $this->params['convey'] = [
            'timeout' => 60,
            'headers' => [
                'Host' => 'ackuna.com',
                'Accept' => '*/*',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Referer' => 'http://translation.conveythis.com/',
                'Origin' => 'http://translation.conveythis.com',
                'Connection' => 'keep-alive',
            ],
            'query' => [
                'type' => 'google' // can also be yandex or bing
            ],
            'cookies' => &$this->cookies['convey']
        ];
    }

    function translate($source, $target, $input)
    {
        if ($this->mtr->arr) {
            $this->preReq($input);
        } else {
            return false;
        }

        $this->params['convey']['query'] =
            array_merge($this->params['convey']['query'],
                ['src' => $source, 'dst' => $target]);

        list($inputs, $str_ar) = $this->genQ($input, 'genReq');
        $res = $this->reqResponse('GET', 'convey', $this->params['convey'], $inputs);
        foreach ($res as $re) {
            $translation[] = html_entity_decode($re);
        }

        $translated = $this->joinTranslated($str_ar, $input, $translation, $this->misc['splitGlue']);

        return $translated;
    }

    function genReq(array $params)
    {
        return ['query' => ['text' => $params['data']]];
    }

    function preReq(array &$input)
    {
        $this->genC('convey');
        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue']);
    }

    function getLangs()
    {
        foreach (json_decode($this->reqResponse('GET', 'conveyL2'), true) as $l) {
            $langs[] = $l['Google'];
        }

        return $langs;
    }

}
