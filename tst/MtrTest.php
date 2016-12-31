<?php
use PHPUnit\Framework\TestCase;
use Mtr\Mtr;

class MtrTest
    extends
    TestCase
{
    public function fields($size)
    {
//        $options = [
//            'systran' => ['key' => 'bumPX7NIxqmshawMILIXKJqBGTUjp1pVQu7jsn5MsDhsPyCku1']
//        ];
        $options = [];
        $source = 'en';
        $target = 'de';
        $services = ['google', 'bing', 'yandex'];
        $keys = [3, 2, 9];

        switch ($size) {
            case 'all' :
                $text = file_get_contents(__DIR__ . '/large.txt');
                $text2 = file_get_contents(__DIR__ . '/short.txt');
                $text3 = file_get_contents(__DIR__ . '/medium.txt');
                break;
            case 'short' :
                $text = file_get_contents(__DIR__ . '/short.txt');
                $text2 = $text;
                $text3 = $text;
                break;
            case 'medium' :
                $text = file_get_contents(__DIR__ . '/medium.txt');
                $text2 = $text;
                $text3 = $text;
                break;
            case 'large' :
                $text = file_get_contents(__DIR__ . '/large.txt');
                $text2 = $text;
                $text3 = $text;
                break;
        }


        return [
            'options' => $options,
            'source' => $source,
            'target' => $target,
            'services' => $services,
            'keys' => $keys,
            'input' => [
                $keys[0] => $text,
                $keys[1] => $text2,
                $keys[2] => $text3
            ]
        ];
    }

    /*
     *
     */
    public function testGeneral()
    {
        $fields = $this->fields('all', 3);
        $mtr = new Mtr($fields['options']);

        $results =
            $mtr->tr($fields['source'], $fields['target'], $fields['input'],
                $fields['services']);

        foreach ($results as $key => $v) {
            $this->assertNotEmpty($v);
        }
    }

    /* @depends testGeneral
     *
     */
    public function testArrays()
    {
        $fields = $this->fields('short', 3);
        $mtr = new Mtr($fields['options']);

        $results =
            $mtr->tr($fields['source'], $fields['target'], $fields['input'],
                $fields['services']);

        $c = 0;
        foreach ($results as $key => $v) {
            $this->assertEquals($fields['keys'][$c], $key);
            $c++;
        }
    }

    /* @depends testGeneral
     *
     */
    public function testBing()
    {
        $fields = $this->fields('short', 3);
        $mtr = new Mtr($fields['options']);

        $results =
            $mtr->tr($fields['source'], $fields['target'], $fields['input'], 'bing');

        foreach ($results as $key => $v) {
            $this->assertNotEmpty($v);
        }
    }

    /* @depends testGeneral
     *
     */
    public function testConvey()
    {
        $fields = $this->fields('short', 3);
        $mtr = new Mtr($fields['options']);

        $results =
            $mtr->tr($fields['source'], $fields['target'], $fields['input'],
                'convey');

        foreach ($results as $key => $v) {
            $this->assertNotEmpty($v);
        }
    }

    /* @depends testGeneral
     *
     */
    public function testFrengly()
    {
        $fields = $this->fields('short', 3);
        $mtr = new Mtr($fields['options']);

        $results =
            $mtr->tr($fields['source'], $fields['target'], $fields['input'],
                'frengly');

        foreach ($results as $key => $v) {
            $this->assertNotEmpty($v);
        }
    }

    /* @depends testGeneral
     *
     */
    public function testGoogle()
    {
        $fields = $this->fields('short', 3);
        $mtr = new Mtr($fields['options']);

        $results =
            $mtr->tr($fields['source'], $fields['target'], $fields['input'],
                'google');

        foreach ($results as $key => $v) {
            $this->assertNotEmpty($v);
        }
    }

    /* @depends testGeneral
     *
     */
    public function testMultillect()
    {
        $fields = $this->fields('short', 3);
        $mtr = new Mtr($fields['options']);

        $results =
            $mtr->tr($fields['source'], $fields['target'], $fields['input'],
                'multillect');

        foreach ($results as $key => $v) {
            $this->assertNotEmpty($v);
        }
    }

    /* @depends testGeneral
     *
     */
    public function testPromt()
    {
        $fields = $this->fields('short', 3);
        $mtr = new Mtr($fields['options']);

        $results =
            $mtr->tr($fields['source'], $fields['target'], $fields['input'],
                'promt');

        foreach ($results as $key => $v) {
            $this->assertNotEmpty($v);
        }
    }

    /* @depends testGeneral
     *
     */
    public function testSdl()
    {
        $fields = $this->fields('short', 3);
        $mtr = new Mtr($fields['options']);

        $results =
            $mtr->tr($fields['source'], $fields['target'], $fields['input'], 'sdl');

        foreach ($results as $key => $v) {
            $this->assertNotEmpty($v);
        }
    }

    /* @depends testGeneral
     *
     */
    public function testSystran()
    {
        $fields = $this->fields('short', 3);
        $mtr = new Mtr($fields['options']);

        try {
            $results =
                $mtr->tr($fields['source'], $fields['target'], $fields['input'],
                    'systran');
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'not active')) {
                {
                    return;
                }
            } else {
                throw $e;
            }
        }

        foreach ($results as $key => $v) {
            $this->assertNotEmpty($v);
        }
    }

    /* @depends testGeneral
     *
     */
    public function testTreu()
    {
        $fields = $this->fields('short', 3);
        $mtr = new Mtr($fields['options']);

        $results =
            $mtr->tr($fields['source'], $fields['target'], $fields['input'], 'treu');

        foreach ($results as $key => $v) {
            $this->assertNotEmpty($v);
        }
    }

    /* @depends testGeneral
     *
     */
    public function testYandex()
    {
        $fields = $this->fields('short', 3);
        $mtr = new Mtr($fields['options']);

        $results =
            $mtr->tr($fields['source'], $fields['target'], $fields['input'],
                'yandex');

        foreach ($results as $key => $v) {
            $this->assertNotEmpty($v);
        }
    }

}