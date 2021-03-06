<?php
namespace Mtr;

use GuzzleHttp\Client;

/**
 * @property TextReq txtrq
 */
class Google
    extends
    Ep
    implements
    Service
{
    public $mtr;
    public $txtrq;

    private $service = 'google';
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
        $this->misc['glue'] = ' ; ¶ ; ';
        $this->misc['splitGlue'] = '/\s?;\s?¶\s?;?\s?/';
        $this->urls['googleL'] = 'https://translate.google.com';
        $this->urls['google'] = 'https://translate.google.com/translate_a/single';
        $this->cookies['google'] = apcu_fetch('mtr_cookies_google');
        $this->params['google'] = [
            'headers' => [
                'Host' => 'translate.google.com',
                'Accept' => '*/*',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Referer' => 'https://translate.google.com/',
                'Connection' => 'keep-alive'
            ],
            'query' => [
                'client' => 't',
                'hl' => 'en',
                'dt' => 't',
                'ie' => 'UTF-8', // Input encoding
                'oe' => 'UTF-8', // Output encoding
                'multires' => 1,
                'otf' => 0,
                'pc' => 1,
                'trs' => 1,
                'ssel' => 0,
                'tsel' => 0,
                'kc' => 1,
            ],
            'cookies' => &$this->cookies['google']
        ];
        $this->misc['googleRegexes'] = ['/,+/' => ',', '/\[,/' => '[',];
    }

    /**
     * @return array
     */
    public function getLangs()
    {
        preg_match_all("/{code:\s?\s?'([a-z]{2,3}(\-[A-Z]{2,4})?)'/",
            $this->reqResponse('GET', 'googleL'), $matches);
        return array_unique($matches[1]);
    }

    public function preReq(&$input)
    {
        $this->genC('google');
        $input = $this->txtrq->pT($input, $this->mtr->arr, $this->misc['glue'], $this->limit, $this->service);
    }

    /**
     * @param array $params
     * @return array
     * @internal param $source
     * @internal param $target
     * @internal param $data
     */
    public function genReq(array $params)
    {
        return [
            'body' => "q=" . urlencode($params['data']),
            'query' => [
                'tk' => $this->generateToken($params['data'])
            ]
        ];
    }

    private function regexJson(&$res, &$epO)
    {
        return json_decode(preg_replace(array_keys($epO->misc['googleRegexes']),
                                        array_values($epO->misc['googleRegexes']), $res));
    }

    private function trJson(&$bodyJson)
    {
        $translation = '';
        foreach ($bodyJson[0] as $rt) {
            $translation .= $rt[0];
        }

        return $translation;
    }

    /**
     *  Google
     *
     * @param string $source language
     * @param string $target language
     * @param mixed $input text
     * @return string ,array
     * @internal param $epO
     */
    public function translate($source, $target, $input)
    {

        $this->preReq($input);
        $bodyJson = null;

        $this->params['google'] =
            array_merge_recursive($this->params['google'],
                ['query' => ['sl' => $source, 'tl' => $target]]);

        list($inputs, $str_ar) = $this->genQ($input, 'genReq');
        $res =
            $this->reqResponse('POST', 'google', $this->params['google'], $inputs);
        foreach ($res as $re) {
            $bodyJson[] = $this->regexJson($re, $this);
        }
        foreach ($bodyJson as $bJ) {
            $translation[] = $this->trJson($bJ);
        }
        $translated =
            $this->joinTranslated($str_ar, $input, $translation,
                $this->misc['splitGlue']);

        return $translated;
    }

    /**
     * Google Token Generator.
     *
     * Thanks to @helen5106 and @tehmaestro and few other cool guys
     * at https://github.com/Stichoza/google-translate-php/issues/32
     *
     * Generate and return a token.
     *
     * @param string $text Text to translate
     * @return mixed A token
     * @internal param $target
     * @internal param $source
     */
    public function generateToken($text)
    {
        return $this->TL($text);
    }

    /**
     * Generate a valid Google Translate request token.
     *
     * @param string $a text to translate
     *
     * @return string
     */
    private function TL($a)
    {
        $tkk = $this->TKK();
        $b = $tkk[0];

        for ($d = [], $e = 0, $f = 0; $f < mb_strlen($a, 'UTF-8'); $f++) {
            $g = $this->charCodeAt($a, $f);
            if (128 > $g) {
                $d[$e++] = $g;
            } else {
                if (2048 > $g) {
                    $d[$e++] = $g >> 6 | 192;
                } else {
                    if (55296 == ($g & 64512) &&
                        $f + 1 < mb_strlen($a, 'UTF-8') &&
                        56320 == ($this->charCodeAt($a, $f + 1) & 64512)
                    ) {
                        $g =
                            65536 +
                            (($g & 1023) << 10) +
                            ($this->charCodeAt($a, ++$f) & 1023);
                        $d[$e++] = $g >> 18 | 240;
                        $d[$e++] = $g >> 12 & 63 | 128;
                    } else {
                        $d[$e++] = $g >> 12 | 224;
                        $d[$e++] = $g >> 6 & 63 | 128;
                    }
                }
                $d[$e++] = $g & 63 | 128;
            }
        }
        $a = $b;
        for ($e = 0; $e < count($d); $e++) {
            $a += $d[$e];
            $a = $this->RL($a, '+-a^+6');
        }
        $a = $this->RL($a, '+-3^+b+-f');
        $a ^= $tkk[1];
        if (0 > $a) {
            $a = ($a & 2147483647) + 2147483648;
        }
        $a = fmod($a, pow(10, 6));

        return $a . '.' . ($a ^ $b);
    }

    /**
     * @return array
     */
    private function TKK()
    {
        return ['406398', (561666268 + 1526272306)];
    }

    /**
     * Process token data by applying multiple operations.
     *
     * @param $a
     * @param $b
     *
     * @return int
     */
    private function RL($a, $b)
    {
        for ($c = 0; $c < strlen($b) - 2; $c += 3) {
            $d = $b[$c + 2];
            $d = $d >= 'a' ? $this->charCodeAt($d, 0) - 87 : intval($d);
            $d = $b[$c + 1] == '+' ? $this->shr32($a, $d) : $a << $d;
            $a = $b[$c] == '+' ? ($a + $d & 4294967295) : $a ^ $d;
        }

        return $a;
    }

    /**
     * Crypto function.
     *
     * @param $x
     * @param $bits
     *
     * @return number
     */
    private function shr32($x, $bits)
    {
        if ($bits <= 0) {
            return $x;
        }
        if ($bits >= 32) {
            return 0;
        }
        $bin = decbin($x);
        $l = strlen($bin);
        if ($l > 32) {
            $bin = substr($bin, $l - 32, 32);
        } elseif ($l < 32) {
            $bin = str_pad($bin, 32, '0', STR_PAD_LEFT);
        }

        return bindec(str_pad(substr($bin, 0, 32 - $bits), 32, '0', STR_PAD_LEFT));
    }

    /**
     * Get the Unicode of the character at the specified index in a string.
     *
     * @param string $str
     * @param int $index
     *
     * @return bool
     */
    private function charCodeAt($str, $index)
    {
        $char = mb_substr($str, $index, 1, 'UTF-8');
        if (mb_check_encoding($char, 'UTF-8')) {
            $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            $result = hexdec(bin2hex($ret));

            return $result;
        }

        return false;
    }
}
