<?php

// transltr
//        $this->urls['transltrL'] =
//            'http://www.transltr.org/api/getlanguagesfortranslate';
//        $this->urls['transltr'] = 'http://www.transltr.org/api/translate';
//        $this->cookies['transltr'] = apc_fetch('mtr_cookies_transltr');
//        $this->params['transltr'] = [
//            'headers' => [
//                'Host' => 'www.transltr.org',
//                'Accept' => 'application/json, text/plain, */*',
//                'Accept-Language' => 'en-US,en;q=0.5',
//                'Accept-Encoding' => 'gzip, deflate',
//                'Referer' => 'http://www.transltr.org/',
//                'Content-Type' => 'application/json;charset=utf-8',
//                'Connection' => 'keep-alive'
//            ],
//            'cookies' => &$this->cookies['transltr']
//        ];

//    private function transltrLangs()
//    {
//        foreach (json_decode($this->reqResponse('GET', 'transltrL'), true) as $re) {
//            $langs[] = $re['languageCode'];
//        }
//
//        return $langs;
//    }
//
//    private function transltrPreReq()
//    {
//        $this->genC('transltr');
//    }
//
//    private function transltrGenReq($params)
//    {
//        return [
//            'json' => [
//                'text' => $params['data']
//            ]
//        ];
//    }
//
//    public function transltr($source, $target, $input)
//    {
//        if ($input[0]) {
//            $this->transltrPreReq();
//        } else {
//            return false;
//        }
//
//        $this->params['transltr']['timeout'] = 120; // transltr is slow...
//        $this->params['transltr']['json'] = ['from' => $source, 'to' => $target];
//
//        list($inputs, $str_ar) = $this->genQ($input, 'transltrGenReq');
//        $res =
//            $this->reqResponse('POST', 'transltr', $this->params['transltr'],
//                $inputs);
//        foreach ($res as $re) {
//            $translation[] = json_decode($re, true)['translationText'];
//        }
//        $translated = $this->joinTranslated($str_ar, $input, $translation);
//
//        return $translated;
//    }
