<?php
namespace Mtr;

class TextReq
{
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
     * @param string ,array $input text
     *
     * @return array
     */
    public function pT($input, &$arr, &$glue)
    {
        if (!$arr) {
            return [[$this->splitP($input, $this->rgxMain)]];
        }
        $arr_input = $parts = [];
        $chars = $p = $a = 0;
        foreach ($input as $key => $str) {
            $strl = mb_strlen($str);
            if ($strl > 1024) {
                $arr_input[$p] =
                    [$key => $this->splitP($input[$key], $this->rgxMain)];
                $p++;
            } elseif ($chars + $strl > 1024) {
                foreach ($parts as $key) {
                    $arr_input[$p][$key] = &$input[$key];
                }
                $arr_input[$p]['s'] = implode($glue, $arr_input[$p]);
                $p++;
                $chars = 0;
                $parts = [];
            } else {
                $chars += $strl;
                $parts[] = $key;
                $a++;
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
        $this->rgxMain = $this->multiRegex('[\S\s]{1,1022}', [
            '\.\s',
            '\;\s',
            '\:\s',
            '\,\s',
            '\n\s',
            '\.',
            '\;',
            '\:',
            '\,',
            '\n',
            ''
        ]);
    }

    private function multiRegex(
        $const,
        $vars
    ) {
        $frags = [];
        foreach ($vars as $r) {
            $frags[] = $const . $r;
        }

        return '/(' . implode('|', $frags) . ')/m';
    }
}