<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 26.05.2016
 * Time: 10:29.
 */

namespace unit\helpers;

use skewer\helpers\Html;

class HtmlTest extends \Codeception\Test\Unit
{
    public function providerHasContent()
    {
        return [
            ['', false],
            ['   ', false],
            ['<img src="HtmlTest.php">', true],
            ['<br>', false],
            ['<br />', false],
            ['<div></div>', false],
            ['<p></p>', false],
            ['<p>asd</p>', true],
            ['a', true],
            ['a', true],
            ["\xE2\x80\x8B", false],
            ["\xE2\x80\x8Basdf", true],
            ["\r\n", false],
        ];
    }

    /**
     * @dataProvider providerHasContent
     * @covers  \skewer\helpers\Html::hasContent
     *
     * @param mixed $sIn
     * @param mixed $bRes
     */
    public function testHasContent($sIn, $bRes)
    {
        $this->assertSame($bRes, Html::hasContent($sIn), "[{$sIn}] is not [" . ($bRes ? 'true' : 'false') . ']');
    }

    /**
     * Часть кода закомментировано, т.к. сейчас не обрабатываются
     * случаи с переводами строк, т.к. это ломало дебаг панель на странице.
     *
     * @return array
     */
    public function providerReplaceLongSpaces()
    {
        return [
            ['', ''],
            ['asd dsa das', 'asd dsa das'],
            ['asd                 asd', 'asd asd'],
            ["asd\r\nasd", "asd\r\nasd"],
            //["asd \r\n  \r\n\r\n \r\n  asd", 'asd asd'],
            ['asd
            asd', 'asd
 asd'],
            ['<p  class="as asd   asd3">asd                 asd</p>', '<p class="as asd asd3">asd asd</p>'],
//            ['ewq               <pre>
//            asd
//            asd
//            </pre>          qwe', 'ewq <pre>
//            asd
//            asd
//            </pre> qwe'],
//            ['ewq               <script>
//            asd
//            asd
//            </script>          qwe', 'ewq <script>
//            asd
//            asd
//            </script> qwe'],
        ];
    }

    /**
     * @covers \skewer\helpers\Html::replaceLongSpaces()
     * @dataProvider providerReplaceLongSpaces
     *
     * @param mixed $in
     * @param mixed $out
     */
    public function testReplaceLongSpaces($in, $out)
    {
        $this->assertSame($out, Html::replaceLongSpaces($in));
    }
}
