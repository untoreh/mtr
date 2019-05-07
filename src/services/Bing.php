<?php
namespace Mtr;

use GuzzleHttp\Client;


/**
 * @property TextReq txtrq
 */
class Bing
    extends
    Ep
    implements
    Service
{

    public $mtr;

    private $service = 'bing';
    private $limit = 4950;

    function __construct(
        Mtr &$mtr,
        Client &$gz,
        TextReq &$txtrq,
        LanguageCode &$ld
    ) {
        parent::__construct($mtr, $gz, $txtrq, $ld);

        $this->txtrq->setRegex($this->service, $this->limit);
        $this->misc['weight'] = 30;
        $this->misc['glue'] = '; ¶; ';
        $this->misc['splitGlue'] = "/;?\s?¶\s?;\s?/";
        $this->urls['bingL'] = 'http://www.bing.com/translator/';
        // $this->urls['bingL'] = 'https://api.cognitive.microsofttranslator.com/languages';
        $this->urls['bing'] = 'https://www.bing.com/ttranslate';
        $this->cookies['bing'] = apcu_fetch('mtr_cookies_bing');
        $this->params['bing'] = [
            'headers' => [
                'Host' => 'www.bing.com',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Referer' => 'https://www.bing.com/translator/',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'X-Requested-With' => 'XMLHttpRequest',
            ],
            'cookies' => &$this->cookies['bing']
        ];
    }

    function translate($source, $target, $input)
    {
        $this->preReq($input);

        if ($source == 'auto') {
            $source = '-';
        }
        $this->params['bing'] = array_merge_recursive( $this->params['bing'], [
            'form_params' => [
                 'from' => $source,
                 'to' => $target
            ]
        ]);
        list($inputs, $str_ar) = $this->genQ($input, 'genReq');

        $res = $this->reqResponse('POST', 'bing', $this->params['bing'], $inputs);
        foreach ($res as $re) {
            $translation[] = json_decode($re, true)['translationResponse'];
        }
        $translated = $this->joinTranslated($str_ar, $input, $translation, $this->misc['splitGlue']);

        return $translated;
    }

    /**
     * genReq
     *
     * @param array $params
     */
    function genReq(array $params)
    {
        return [
            'form_params' => ['text' => $params['data']]
        ];
    }

    /**
     * preReq
     *
     * @param mixed $input
     */
    function preReq(&$input)
    {
        $this->genC('bing');
        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue'], $this->limit, $this->service);
    }

    function getLangs()
    {
        preg_match_all('/value="?([a-z]{2,3}(\-[A-Z]{2,4})?)"?>/',
           $this->reqResponse('GET', 'bingL'), $matches);
        // $langs = array();
        // $res = $this->reqResponse('GET', 'bingL',
        //                           array_merge_recursive($this->params['bing'],
        //                                                 array(
        //                                                     "query" => [
        //                                                         "scope" => "translation"
        //                                                     ]
        //                                                 )));
        // foreach (json_decode($res)->translation as $k => $v) {
        //     $langs[] = $k;
        // }
        // return $langs;
        return $matches[1];
    }

}
