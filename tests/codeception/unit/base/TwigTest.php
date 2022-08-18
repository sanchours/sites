<?php

namespace unit\base;

use skewer\base\SysVar;
use skewer\base\Twig;

/**
 * Created by PhpStorm.
 * User: sam
 * Date: 13.08.15
 * Time: 14:26.
 */
class TwigTest extends \Codeception\Test\Unit
{
    /**
     * @covers \skewer\base\Twig::priceFormat
     */
    public function testPriceFormat()
    {
        $bOldVal = SysVar::get('catalog.hide_price_fractional');

        SysVar::set('catalog.hide_price_fractional', false);

        $this->assertEquals(Twig::priceFormat('123'), '123.00');
        $this->assertEquals(Twig::priceFormat('1234'), '1 234.00');
        $this->assertEquals(Twig::priceFormat('1234567'), '1 234 567.00');
        $this->assertEquals(Twig::priceFormat('123.123'), '123.12');
        $this->assertEquals(Twig::priceFormat('1234.3'), '1 234.30');
        $this->assertEquals(Twig::priceFormat('1234567.56'), '1 234 567.56');

        SysVar::set('catalog.hide_price_fractional', true);

        $this->assertEquals(Twig::priceFormat('123'), '123');
        $this->assertEquals(Twig::priceFormat('1234'), '1 234');
        $this->assertEquals(Twig::priceFormat('1234567'), '1 234 567');

        SysVar::set('catalog.hide_price_fractional', $bOldVal);
    }
}
