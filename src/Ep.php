<?php
namespace Mtr;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Cookie\CookieJar;
use \Campo\UserAgent;
use GuzzleHttp\Psr7\Response;


/**
 * Endpoint generator
 *
 *
 * @property TextReq txtrq
 * @property CookieJar cookies
 */
abstract class Ep
{
    public $urls;
    public $params;
    public $misc;

    /*
     * @var boolean $active
     */
    public $active;

    /**
     *  D
     *
     * @param Mtr $mtr
     * @param Client $gz
     * @param TextReq $txtrq
     * @param LanguageCode $ld
     */
    public function __construct(
        Mtr &$mtr,
        Client &$gz,
        TextReq &$txtrq,
        LanguageCode &$ld
    ) {
        if ($mtr) {
            $this->mtr = &$mtr;
        }
        $this->gz = &$gz;
        $this->txtrq = &$txtrq;
        $this->ld = &$ld;
        $this->defaults();
    }

    /**
     *  defaults
     *
     * @return void
     */
    private function defaults()
    {
        //default
        $ua = apcu_fetch('mtr_ua_rnd');
        if (!$ua) {
            $ua = UserAgent::random([
                'os_type' => 'Windows',
                'device_type' => 'Desktop',
                'agent_name' => 'Chrome'
            ]);
            apcu_store('mtr_ua_rnd', $ua, 3600);
        }
        $this->params['default'] = [
            'headers' => [
                'User-Agent' => &$ua
            ],
            'query' => []
        ];

        $this->misc['glue'] = $this->mtr->glue;
        $this->misc['splitGlue'] = $this->mtr->splitGlue;

        $this->active = true;
    }

    /**
     * Generate cookies
     * @param $service
     * @return bool
     */
    public function genC($service)
    {
        if (!$this->cookies[$service]) {
            $this->cookies[$service] = new CookieJar();
            // generate the cookies
            $this->reqResponse('GET', $service . 'L',
                ['cookies' => $this->cookies[$service]]);
            apcu_store('mtr_cookies_' . $service, $this->cookies[$service],
                $this->ttl());
        }

        return true;
    }

    /**
     * Generate array of requests queries
     *
     * @param array ,string $input
     * @param $genReqFun
     * @return array
     * @internal param function $genReq
     *
     */
    public function genQ(&$input, $genReqFun)
    {
        $str_ar = []; // 1 for string, 0 for array
        $inputs = [];
        foreach ($input as $key => $input_part) {
            if (is_array($input_part)) {
                if ( count($input_part) > 1) { // we pass the imploded 's' str
                    $str_ar[$key] = array_keys($input_part);
                    $inputs[] = $this->$genReqFun([
                        'source' => &$this->mtr->source,
                        'target' => &$this->mtr->target,
                        'data' => &$input_part['s']
                    ]);
                } else {
                    // this runs once
                    foreach ($input_part as $k => $input_frag) {
                        $str_ar[$key] = $k;
                        foreach ($input_frag as $frag) {
                            $inputs[] = $this->$genReqFun([
                                'source' => &$this->mtr->source,
                                'target' => &$this->mtr->target,
                                'data' => &$frag
                            ]);
                        }
                    }
                }
            } else {
                $inputs[] = $this->$genReqFun([
                    'source' => &$this->mtr->source,
                    'target' => &$this->mtr->target,
                    'data' => &$input_part
                ]);
            }

        }

        return [$inputs, $str_ar];
    }

    private function sp_string($str, $reg = null)
    {
        if ($reg) {
            return preg_split($reg, $str, -1);
        } else {
            return explode($this->mtr->glue, $str);
        }
    }

    /**
     * Merge split strings as they were received
     *
     * @param $str_ar
     * @param $input
     * @param $translation
     * @param string $glue
     * @return array
     */
    public function joinTranslated(&$str_ar, &$input, &$translation, $glue = null)
    {
        if (!$glue) {
            $glue = &$this->mtr->splitGlue;
        }
        $str_p = 0;
        $translated = [];
        foreach ($str_ar as $key => $k) {
            if (is_int($k)) {
                $hop = count($input[$key][$k]);
                $translated[$k] =
                    implode('', array_slice($translation, $str_p, $hop));
                $str_p += $hop;
            } else {
                array_pop($k); // remove the 's' key which should be last
                $expl = $this->sp_string($translation[$str_p], $glue);
                $str_p++;
                $c = 0;
                foreach ($k as $kk) {
                    $translated[$kk] = $expl[$c];
                    $c++;
                }
            }
        }

        return $translated;
    }

    public function isActive()
    {
        if ($this->active) {
            return true;
        } else {
            return false;
        }
    }

    public function ttl()
    {
        return mt_rand(600, 6000);
    }

    /**
     *  Merge options
     *
     * @param array $options list
     *
     * return array
     * @return array
     */
    public function options($options)
    {
        return array_merge_recursive($options, $this->params['default']);
    }

    /**
     *  Call the api
     *
     * @param $type
     * @param string $service name
     * @param array $options list
     * @param array $inputs
     * @return array|bool
     * @internal param array $input multiple queries
     */
    public function reqResponse(
        $type,
        $service,
        array $options = [],
        array $inputs = null
    ) {
        if ($inputs) {
            $retries = 0;
            $arrRes = [];
            do {
                $promises = [];
                foreach ($inputs as $in) {
//                    $dbg = array_merge_recursive($this->options($options), $in);
//                    $url = $this->urls[$service];
//                    xdebug_break();
                    // error_log(print_r($this->urls[$service], 1));
                    // error_log(print_r(array_merge_recursive($this->options($options), $in), 1));
                    // exit;
                    // $stuff = $this->gz->request($type, $this->urls[$service],
                    //                             array_merge_recursive($this->options($options), $in));
                    // error_log(print_r((string)$stuff->getBody(), 1));

                    $promises[] =
                        $this->gz->requestAsync($type, $this->urls[$service],
                            array_merge_recursive($this->options($options), $in));
                }
                $results = Promise\unwrap($promises);
                /* @var Response $res */
                foreach ($results as $key => $res) {
                    $strRes = (string)$res->getBody();
                    if ($res->getStatusCode() === 200) {
                        unset($inputs[$key]);
                        $arrRes[$key] = $strRes;
                    }
                }
            } while (count($inputs) > 0 && $retries++ < 3);

            return $arrRes;
        } elseif ($type) {
            $retries = 0;
            do {
                $res =
                    $this->gz->request($type, $this->urls[$service],
                        $this->options($options));
            } while ($res->getStatusCode() !== 200 && $retries++ < 3);

            return (string)$res->getBody();
        } else {
            return false;
        }
    }

}
