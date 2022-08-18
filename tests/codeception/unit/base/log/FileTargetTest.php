<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 19.01.2018
 * Time: 11:27.
 */

namespace tests\codeception\unit\base\log;

use skewer\base\log\FileTarget;

class FileTargetTest extends \Codeception\Test\Unit
{
    public function providerFormatMessage()
    {
        return [
            [false, '(bool): FALSE'],
            [true, '(bool): TRUE'],
            [123, '(int): 123'],
            [0, '(int): 0'],
            [-456, '(int): -456'],
            [12.21, '(double): 12.21'],
            [7E-10, '(double): 7.0E-10'],
            ['', '(str): '],
            ['---', '(str): ---'],
            ['йцу', '(str): йцу'],
            ['qwe qwe', '(str): qwe qwe'],
            [[[1], 2, 3, 4], '(array): Array
(
    [0] => Array
        (
            [0] => 1
        )

    [1] => 2
    [2] => 3
    [3] => 4
)
'],
            [new ClassA(), '(obj): tests\codeception\unit\base\log\ClassA Object
(
    [a] => 1
)
'],
            [null, '(null): NULL'],
        ];
    }

    /**
     * @dataProvider providerFormatMessage
     *
     * @param $in
     * @param $out
     */
    public function testFormatMessage($in, $out)
    {
        $o = new FileTarget();

        $val = $o->formatMessage([$in]);

        // проверяем наличие даты
        $this->assertRegExp('/^\[[\w+,:\s]+\]/', $val);

        $val = mb_substr($val, mb_strpos($val, ']') + 1);

        $this->assertSame($out, $val);
    }

    public function testFormatMessageRes()
    {
        $path = WEBPATH . 'files/test_res.txt';
        $h = fopen($path, 'w+');

        $o = new FileTarget();

        $val = $o->formatMessage([$h]);

        fclose($h);
        unlink($path);

        // проверяем наличие даты
        $this->assertRegExp('/^\[[\w+,:\s]+\]/', $val);

        $val = mb_substr($val, mb_strpos($val, ']') + 1);

        $this->assertRegExp('/\(res\): Resource id #\d+/', $val);
    }
}

class ClassA
{
    public $a = 1;
}
