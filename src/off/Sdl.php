<?php
namespace Mtr;

use GuzzleHttp\Client;

/**
 * @property TextReq txtrq
 */
class Sdl
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
        $this->urls['sdlL'] = 'https://www.freetranslation.com/en/';
        $this->urls['sdl'] =
            'https://api.freetranslation.com/freetranslation/translations/text';
        $this->params['sdl'] = [
            'headers' => [
                'Host' => 'api.freetranslation.com',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Referer' => 'https://www.freetranslation.com/',
                'Content-Type' => 'application/json',
                'Origin' => 'https://www.freetranslation.com',
                'Connection' => 'keep-alive'
            ]
        ];
    }

    function translate($source, $target, $input)
    {
        $this->preReq($input);

        $this->params['sdl']['json'] = ['from' => $source, 'to' => $target];

        list($inputs, $str_ar) = $this->genQ($input, 'genReq');

        $res = $this->reqResponse('POST', 'sdl', $this->params['sdl'], $inputs);
        foreach ($res as $re) {
            $translation[] = json_decode($re, true)['translation'];
        }
        $translated = $this->joinTranslated($str_ar, $input, $translation, $this->misc['splitGlue']);

        return $translated;
    }

    function genReq(array $params)
    {
        return ['json' => ['text' => $params['data']]];
    }

    function preReq(&$input)
    {
        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue']);
    }

    function getLangs()
    {
        preg_match('/src="(.*common.*?\.js)">/', $this->reqResponse('GET', 'sdlL'),
            $matches);
        $this->urls['sdlL1'] = $matches[1];
        preg_match_all('/code:"(.*?)"/m', $res = $this->reqResponse('GET', 'sdlL1'),
            $matches);

        return array_values(array_unique($matches[1]));
    }

}
