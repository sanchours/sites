<?php
/**
 * Created by PhpStorm.
 * User: koval_000
 * Date: 09.11.2018
 * Time: 17:11.
 */

namespace unit\helpers;

class StringHelperTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider textsDataProvider
     * @covers       \skewer\helpers\StringHelper::truncate
     *
     * @param string $sInText
     * @param int $iInLength
     * @param string $sInSuffix
     * @param mixed $mInEncoding
     * @param string $bInHtml
     * @param string $sOutText
     */
    public function testTruncate($sInText, $iInLength, $sInSuffix, $mInEncoding, $bInHtml, $sOutText)
    {
        $this->assertEquals($sOutText, \skewer\helpers\StringHelper::truncate($sInText, $iInLength, $sInSuffix, $mInEncoding, $bInHtml));
    }

    public function textsDataProvider()
    {
        return [
            [
                'самый умный человек', 8, '...', null, true,
                'самый...',
            ],
            [
                'самый умный человек', 11, '...', null, true,
                'самый...',
            ],
            [
                'самый умный человек', 16, '...', null, true,
                'самый умный...',
            ],
            [
                'самый умный человек', 5000, '...', null, true,
                'самый умный человек',
            ],
            [
                '', 5000, '...', null, true,
                '',
            ],
            [
                '<p>Это первая страница, которую пользователь видит на вашем сайте.</p>', 36, '<a href="[251]">...</a>', null, true,
                '<p>Это первая страница, которую<a href="[251]">...</a></p>',
            ],
            [
                '<p>Это первая страница, которую </p><p>пользователь видит на вашем сайте.</p>', 45, '<a href="[251]">...</a>', null, true,
                '<p>Это первая страница, которую </p><p>пользователь<a href="[251]">...</a></p>',
            ],
        ];
    }
}
