<?php
namespace Mtr;

use GuzzleHttp\Client;

/**
 * @property TextReq txtrq
 */
class Systran
    extends
    Ep
    implements
    Service
{

    public $mtr;
    public $txtrq;
    public $active;

    private $service = 'systran';
    private $limit = 1000;

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
        $this->txtrq->setRegex($this->service, $this->limit);
        $this->misc['weight'] = 10;
        $this->urls['systran'] =
            'https://api-platform.systran.net/translation/text/translate';
        $this->urls['systranL'] =
            'https://api-platform.systran.net/translation/supportedLanguages';
        $this->params['systran'] = [
            'headers' => [
                "Accept" => "application/json"
            ],
            'query' => ['key' => $this->misc['systran_key']]
        ];
    }

    function genReq(array $params)
    {
        return ['query' => ['input' => $params['data']]];
    }

    function translate($source, $target, $input)
    {
        $this->preReq($input);

        $this->params['systran']['query']['source'] = $source;
        $this->params['systran']['query']['target'] = $target;

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

    function preReq(&$input)
    {
        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue'], $this->limit, $this->service);
    }

    function getLangs()
    {
        $langs = [];
        array_merge($this->params['systran']['query'], ['target' => 'en']);
        foreach (json_decode($this->reqResponse('GET', 'systranL',
            $this->params['systran']), true)['languagePairs'] as $re) {
            $langs[] = $re['source'];
        }

        return $langs;
    }

}
