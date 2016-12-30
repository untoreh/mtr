<?php
/**
 * Language code conversions
 *
 * @link        https://github.com/leodido/langcode-conv
 * @copyright   Copyright (c) 2014, Leo Di Donato
 * @license     http://opensource.org/licenses/ISC      ISC license
 *
 * @author Leo Di Donato
 * @auhtor untoreh
 */
namespace Mtr;

/**
 * Class LanguageCodeOptions
 */
class LanguageCodeOptions
{
    protected $config = [
        'output' => [
            'name',
            'native',
            'iso639-1',
            'iso639-2/t',
            'iso639-2/b',
            'iso639-3'
        ]
    ];
    protected $out = null;

    function __construct($out = 'iso639-1')
    {
        $this->setOutput($out);
    }

    /**
     * Set output option
     *
     * @param $output
     * @return $this
     */
    public function setOutput($out)
    {
        if (in_array($out, $this->config['output'])) {
            $this->out = $out;
        } else {
            throw new \Exception('Malformed or not supported language code.');
        }
    }

    /**
     * Retrieve output option
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->out;
    }
}
