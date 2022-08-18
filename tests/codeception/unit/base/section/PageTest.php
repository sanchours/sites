<?php

namespace unit\base\section;

use skewer\base\section\Page;
use skewer\base\section\Parameters;
use skewer\base\section\params\Type;

class PageTest extends \Codeception\Test\Unit
{
    protected function setUp()
    {
        Page::init(0);

        parent::setUp();
    }

    /**
     * @covers \skewer\base\section\Page::getVal()
     * @covers \skewer\base\section\Page::getShowVal()
     * @covers \skewer\base\section\Page::getGroups()
     */
    public function testGetVal()
    {
        /*
         * тест:
         * Засетить раздел
         * Вытаскивать его параметры и сверять
         * проверить системные разделы
         */

        $this->assertFalse(Page::getVal('test', 'test'));
        $this->assertFalse(Page::getShowVal('test', 'test'));

        $iSection = 1234567;

        $sLang = \Yii::$app->language;

        \Yii::$app->sections->setSection('test1', 'title', 11, $sLang);
        \Yii::$app->sections->setSection('test2', 'title', 22, $sLang);
        \Yii::$app->sections->setSection('test3', 'title', 33, $sLang);

        Parameters::setParams($iSection, 'g1', 'n1', 'test1', 'sv1', 't1', Type::paramServiceSection);
        Parameters::setParams($iSection, 'g1', 'n2', 'v2', 'sv2', 't1', 0);
        Parameters::setParams($iSection, 'g1', 'n3', 'v3', 'sv3', 't1', 0);
        Parameters::setParams($iSection, 'g2', 'n1', 'v4', 'sv4', 't1', 0);
        Parameters::setParams($iSection, 'g2', 'n4', 'test2', 'sv5', 't1', Type::paramServiceSection);

        Parameters::setParams(222, 'g1', 'n1', 'v8', 'test3', 't1', Type::paramServiceSection);
        Parameters::setParams(222, 'g1', 'n9', 'v9', '123', 't1', 0);

        $this->assertFalse(Page::getVal('g1', 'n1'));
        $this->assertFalse(Page::getShowVal('g1', 'n1'));

        Page::init($iSection);

        $this->assertEquals(Page::getVal('g1', 'n2'), 'v2');
        $this->assertEquals(Page::getShowVal('g1', 'n2'), 'sv2');

        /* Системный раздел */
        $this->assertEquals(Page::getVal('g1', 'n1'), 11);
        $this->assertEquals(Page::getShowVal('g1', 'n1'), 'sv1');

        $this->assertFalse(Page::getVal('g3', 'n1'));
        $this->assertFalse(Page::getShowVal('g1', 'n6'));

        $aParams = Page::getByGroup('g1');

        $this->assertEquals(count($aParams), 3);

        $this->assertEquals(count(Page::getGroups()), 2);

        $this->assertEquals($aParams['n1']['value'], 11); //должно быть уже обработано!
        $this->assertEquals($aParams['n2']['value'], 'v2');
        $this->assertEquals($aParams['n3']['value'], 'v3');

        Page::init(123);

        $this->assertFalse(Page::getVal('g1', 'n1'));
        $this->assertFalse(Page::getShowVal('g1', 'n1'));
    }
}
