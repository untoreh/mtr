<?php
namespace Mtr;

class TextReq
{
    public $rgxMain;

    function __construct()
    {
        $this->initRegex();
    }

    /**
     *  Process text, splits the text requested (array or string), so that each
     *  part is less than max=1024, by joining smaller strings and splitting
     *  larger strings, every terminal string in the returned array will be a
     *  request body.
     *
     * @param $input
     * @param $arr
     * @param $glue
     * @param $service needed to fetch correct regex string
     * @param $limit max chars for splitting
     * @return array
     * @internal param $string ,array $input text
     */
    public function pT($input, &$arr, &$glue, int $limit = 1000, string $service)
    {
        if (!$arr) {
            return [[$this->splitP($input, $this->rgxService[$service])]];
        }
        $arr_input = $parts = [];
        $chars = $p = $a = 0;
        foreach ($input as $key => $str) {
            $strl = mb_strlen($str);
            if ($strl > $limit) {
                $arr_input[$p] =
                    [$key => $this->splitP($input[$key], $this->rgxService[$service])];
                $p++;
            } elseif ($chars + $strl > $limit) {
                foreach ($parts as $kp) {
                    $arr_input[$p][$kp] = &$input[$kp];
                }
                $arr_input[$p]['s'] = implode($glue, $arr_input[$p]);
                $p++;
                $chars = $strl;
                $parts = [$key];
            } else {
                $chars += $strl;
                $parts[] = $key;
            }
        }
        if ($chars > 0) {
            foreach ($parts as $key) {
                $arr_input[$p][$key] = $input[$key];
            }
            $arr_input[$p]['s'] = implode($glue, $arr_input[$p]);
        }
        return $arr_input;
    }

    /**
     *  split to punctuation < 1024 chars
     *
     * @param $str
     * @param string $reg
     * @return array
     * @internal param $string
     */
    private function splitP(
        $str,
        $reg
    ) {
        return preg_split($reg, $str, -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }

    private function initRegex()
    {
        // $this->rgxMain = $this->multiRegex('[\S\s]{1,1022}', [
        //     '\.\s',
        //     '\;\s',
        //     '\:\s',
        //     '\,\s',
        //     '\n\s',
        //     '\.',
        //     '\;',
        //     '\:',
        //     '\,',
        //     '\n',
        //     ''
        // ]);
        $this->rgxMain = "/([\S\s]{1,1000}[\.\;\:\,\!\?\Z][\s]?)/m";
    }

    public function setRegex(string $service, int $limit = 1000)
    {
        $this->rgxService[$service] = "/([\S\s]{1,$limit}[\.\;\:\,\!\?\Z][\s]?)/m";
    }

    // private function multiRegex(
    //     $const,
    //     $vars
    // ) {
    //     $frags = [];
    //     foreach ($vars as $r) {
    //         $frags[] = $const . $r;
    //     }

    //     return '/(' . implode('|', $frags) . ')/m';
    // }
}
