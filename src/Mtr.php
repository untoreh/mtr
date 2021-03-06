<?php
/**
 * Multi-api translate
 *
 * PHP version hhvm
 *
 * @author   untoreh <contact@unto.re>
 * @link     localhost
 **/
namespace Mtr;

/*
 * APC-APCu hhvm compatibility
 */
if (substr(phpversion(), -4) == 'hhvm') {
    function apcu_store($k, $v)
    {
        return apc_store($k, $v);
    }

    function apcu_fetch($k)
    {
        return apc_fetch($k);
    }
}

use GuzzleHttp\Client;

/**
 * Multi-api translate
 *
 * @property LanguageCode ld
 * PHP version hhvm
 */
class Mtr
{
    public $gz = null;
    public $in = null;
    public $arr = false;
    public $services = [];
    public $defGlue = " ; ; ";
    public $defSplitGlue = "/\s*;\s*;\s*/"; // regex ¶
    public $glue;
    public $ep = null;
    public $merge = true;
    public $splitGlue;
    public $matrix;
    public $txtrq;
    public $srv;
    public $httpOpts;
    public $options;
    public $target;
    public $source;

    public function __construct($options = ['request' => []])
    {
        /* setup shared variables like glue */
        $this->assignVariables($options);
        /* setup services objects */
        $this->makeServices();
        /* setup the language matrix to dispatch correct services
         for requested languages */
        $this->langMatrix();
    }

    /**
     *  Translate
     *
     * @param string $source the origin language
     * @param string $target the language to tranlate to
     * @param $input
     * @param $service
     * @return string ,array
     * @throws \Exception
     * @internal param $string ,array $input  the input text
     */
    public function tr($source, $target, $input, $service = null)
    {
        if (empty($input)) {
            return false;
        }

        $this->source = &$source;
        $this->target = &$target;

        $service = $this->pickService($service);

        /* Exceptions here are not really supposed to be throwed because @pickService
         makes sure the service supports the language pair */
        if (!$source = $this->langToSrv($source, $service)) {
            throw new \Exception("Language $source not supported by $service");
        }
        if (!$target = $this->langToSrv($target, $service)) {
            throw new \Exception("Language $target not supported by $service");
        }

        if ($this->arr = is_array($input)) {
            return array_replace($input,
                $this->srv->$service->translate($source, $target, $input));
        } else {
            return $this->srv->$service->translate($source, $target, $input)[0];
        }
    }

    /*
     * convert language code to service equivalent
     */
    private function langToSrv($lang, $srv)
    {
        if ($langts = apcu_fetch("mtr_${srv}_langs_conv")) {

            return $langts[$lang];
        }
        if (!$srvLangs = apcu_fetch("mtr_${srv}_langs")) {
            $srvLangs = $this->srv->$srv->getLangs();
            apcu_store("mtr_${srv}_langs", $srvLangs);
        }

        $cLang = '';
        foreach ($srvLangs as $l) {
            $c = $this->ld->convert($l);
            $langts[$c] = $l;
            if ($lang == $c) {
                $cLang = $l;
            }
        }
        apcu_store("mtr_${srv}_langs_conv", $langts);

        return $cLang;
    }

    /*
     * generate matrix of language code conversion for services
     */
    private function langMatrix()
    {
        if (!$this->matrix = apcu_fetch('mtr_matrix')) {

            foreach ($this->srv as $name => &$obj) {
                /* @var Service|Ep $obj */
                if ($obj->active === true) {
                    foreach ($obj->getLangs() as $l) {
                        $this->matrix[$this->ld->convert($l)][$name] = $l;
                    }
                }
            }
            apcu_store('mtr_matrix', $this->matrix);
        }
    }

    private function pickService($inputServices)
    {
        $services = [];
        if (!$inputServices) {
            foreach ($this->services as $name => $p) {
                if (!$services[$name] = $this->srv->$name->misc['weight']) {
                    $services[$name] = 10;
                }
            }
        } else if (is_string($inputServices)) {
            $inputServices = ucfirst($inputServices);
            if (!$this->srv->$inputServices->active) {
                throw new \Exception("Service [$inputServices] not active, provide keys.");
            }
            if ($this->matrix[$this->source][$inputServices] &&
                $this->matrix[$this->target][$inputServices]
            ) {
                return $inputServices;
            } else {
                throw new \Exception("language codes: [$this->source] or [$this->target] not available for the service: [$inputServices]");
            }
        }

        foreach ((array) $inputServices as $k => $v) {
            if (is_string($k)) {
                $services[ucfirst($k)] = $v;
            } else {
                $name = ucfirst($v);
                if (!$services[$name] = $this->srv->$name->misc['weight']) {
                    $services[$name] = 10;
                }
            }
        }
        foreach ($services as $n => $w) {
            if (!isset($this->matrix[$this->source][$n]) ||
                !isset($this->matrix[$this->target][$n])
            ) {
                unset($services[$n]);
            }
        }
        if (empty($services)) {
            throw new \Exception("No service supplied provides the language translation requested.");
        }
        $r = mt_rand(0, array_sum($services));
        foreach ($services as $name => $s) {
            $r = $r - $s;
            if ($r < 0) {
                return $name;
            }
        }

        return false;
    }

    function assignVariables(&$options)
    {
        // default glue
        $this->glue = $this->defGlue;
        $this->splitGlue = $this->defSplitGlue;

        // default http options
        $this->httpOpts = [
            'http_errors' => true,
            'connect_timeout' => 30,
            'timeout' => 30
        ];

        // custom options

        $this->options = &$options;
    }

    function makeServices()
    {
        // http client
        if (isset($this->options['request'])) {
            $this->httpOpts =
                array_merge($this->httpOpts, $this->options['request']);
        }
        $this->gz = new Client($this->httpOpts);
        // strings operator
        $this->txtrq = new TextReq();
        // language detector
        $this->ld = new LanguageCode();

        // generate services from the services dir
        $this->srv = new \stdClass();
        if ($this->services = apcu_fetch('mtr_services')) {
            foreach ($this->services as $name => $class) {
                $this->srv->$name =
                    new $class($this, $this->gz, $this->txtrq, $this->ld);
            }
        } else {
            foreach (glob(dirname(__FILE__) . '/services/*.php') as $p) {
                $name = pathinfo($p, PATHINFO_FILENAME);
                $class = '\\' . __NAMESPACE__ . '\\' . $name;
                $this->srv->$name =
                    new $class($this, $this->gz, $this->txtrq, $this->ld);
                $this->services[$name] = $class;
                apcu_store('mtr_services', $this->services);
            }
        }
    }

    function supLangs()
    {
        return array_keys($this->matrix);
    }

    function __destruct()
    {
    }

}
