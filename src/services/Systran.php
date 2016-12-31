<?php
namespace Mtr;

use GuzzleHttp\Client;

class Systran
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

        if (!$this->misc['systran_key'] = &$this->mtr->options['systran']['key']) {
            $this->active = false;
        }
        $this->misc['weight'] = 10;
        $this->urls['systran'] =
            'https://systran-systran-platform-for-language-processing-v1.p.mashape.com/translation/text/translate';
        $this->urls['systranL'] =
            'https://systran-systran-platform-for-language-processing-v1.p.mashape.com/translation/supportedLanguages';
        $this->params['systran'] = [
            'headers' => [
                "X-Mashape-Key" => $this->misc['systran_key'],
                "Accept" => "application/json"
            ]
        ];
    }

    function genReq(array $params)
    {
        return ['query' => ['input' => $params['data']]];
    }

    function translate($source, $target, $input)
    {
        if ($this->mtr->arr) {
            $this->preReq($input);
        } else {
            return false;
        }
        $this->params['systran']['query'] =
            ['source' => $source, 'target' => $target];

        list($inputs, $str_ar) = $this->genQ($input, 'genReq');
        $res =
            $this->reqResponse('GET', 'systran', $this->params['systran'], $inputs);
        foreach ($res as $re) {
            $translation[] = json_decode($re, true)['outputs'][0]['output'];
        }
        $translated =
            $this->joinTranslated($str_ar, $input, $translation,
                $this->misc['splitGlue']);

        return $translated;

    }

    function preReq(array &$input)
    {
        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue']);
    }

    function getLangs()
    {
        $this->params['systran']['query'] = ['target' => 'en'];
        foreach (json_decode($this->reqResponse('GET', 'systranL',
            $this->params['systran']), true)['languagePairs'] as $re) {
            $langs[] = $re['source'];
        }

        return $langs;
    }

}
