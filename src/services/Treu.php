<?php
namespace Mtr;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SetCookie;

class Treu
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
        $this->urls['treuL'] = 'http://itranslate4.eu/api/';
        $this->urls['treu'] = 'http://itranslate4.eu/csa';
        $this->cookies['treu'] = apc_fetch('mtr_cookies_treu');
        $this->params['treu'] = [
            'headers' => [
                'Host' => 'itranslate4.eu',
                'Accept' => '*/*',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Referer' => 'http://itranslate4.eu/en/',
                'x-requested-with' => 'XMLHttpRequest',
                'Connection' => 'keep-alive'
            ],
            'query' => [
                'func' => 'translate',
                'origin' => 'text'
            ],
            'cookies' => &$this->cookies['treu']

        ];
        $this->misc['treu_req_data'] = [
            'dom' => '',
            'type' => 'text',
            'trs_open_count' => 6,
            'trs_max_count' => 100
        ];
    }

    function translate($source, $target, $input)
    {
        if ($this->mtr->arr) {
            $this->preReq($input);
        } else {
            return false;
        }

        $this->cookies['treu']->setCookie(new SetCookie([
            'Name' => 'langPair',
            'Value' => $source . '-' . $target,
            'Domain' => 'itranslate4.eu'
        ]));

        $this->misc['treu_req_data'] = array_merge($this->misc['treu_req_data'], [
            'src' => $source,
            'trg' => $target,
            'uid' => $this->cookies['treu']->toArray()[0]['Value']
        ]);
        list($inputs, $str_ar) = $this->genQ($input, 'genReq');
        $res = $this->reqResponse('GET', 'treu', $this->params['treu'], $inputs);
        $tids = [];
        foreach ($res as $re) {
            $tids[] = json_decode($re)->tid;
        }

        $this->params['treu']['query'] = [
            'func' => 'translate_poll',
        ];
        $inputs = [];
        foreach ($tids as $key => $tid) {
            $inputs[$key]['query']['data'] = json_encode(['tid' => $tid]);
            $inputs[$key]['query']['rand'] = (float)rand() / (float)getrandmax();
        }
        $res = $this->reqResponse('GET', 'treu', $this->params['treu'], $inputs);
        foreach ($res as $re) {
            $mw = '';
            foreach (json_decode($re, true)['dat'] as $prov) {
                if ($prov['sgms']) {
                    foreach ($prov['sgms'] as $sgm) {
                        foreach ($sgm['units'] as $txp) {
                            $mw .= $txp['text'];
                        }
                    }
                    break;
                }
            }
            $translation[] = stripslashes($mw);
        }
        $translated = $this->joinTranslated($str_ar, $input, $translation, $this->misc['splitGlue']);

        return $translated;
    }

    function genReq(array $params)
    {
        return [
            'query' => [
                'data' => json_encode(array_merge($this->misc['treu_req_data'],
                    ['dat' => $params['data']]))
            ]
        ];
    }

    function preReq(array &$input)
    {
        $c = $this->genC('treu');
        if ($c) {
            $this->cookies['treu']->setCookie(new SetCookie([
                'Name' => 'acceptCookies',
                'Value' => 'Y',
                'Domain' => 'itranslate4.eu'
            ]));
            $this->cookies['treu']->setCookie(new SetCookie([
                'Name' => 'PLAY_LANG',
                'Value' => 'en',
                'Domain' => 'itranslate4.eu'
            ]));
            apc_store('mtr_cookies_treu', $this->cookies['treu'], $this->ttl());
        }
        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue']);
    }

    function getLangs()
    {
        preg_match('/{"src":(.*?)]/',
            $this->reqResponse('GET', 'treuL', $this->params['treu']), $matches);
        preg_match_all('/"(.*?)"/', $matches[1], $matches);

        return $matches[1];
    }
}
