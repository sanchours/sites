<?php

namespace unit\build\Adm\HTMLBanners;

use skewer\base\section\Tree;
use skewer\build\Adm\HTMLBanners\models\Banners;

class HTMLBannersTest extends \Codeception\Test\Unit
{
    protected function setUp()
    {
        Banners::deleteAll([]);

        parent::setUp();
    }

    /**
     * @covers \skewer\build\Adm\HTMLBanners\models\Banners::getBanners
     * @covers \skewer\build\Adm\HTMLBanners\models\Banners::getBlankBanner
     */
    public function testGetBannersOnInternal()
    {
        /** Добавим разделы */
        $oTree = Tree::addSection(\Yii::$app->sections->main(), 'test1', \Yii::$app->sections->tplNew());
        $iSection1 = $oTree->id;
        $oTree = Tree::addSection($iSection1, 'test1', \Yii::$app->sections->tplNew());
        $iSection2 = $oTree->id;
        $oTree = Tree::addSection($iSection2, 'test1', \Yii::$app->sections->tplNew());
        $iSection3 = $oTree->id;

        /** Добавим баннеры */
        /*Баннер на все страницы*/
        $oBanner = Banners::getBlankBanner();
        $oBanner->setAttributes([
            'section' => $iSection1,
            'on_main' => 1,
            'on_allpages' => 1,
            'on_include' => 1,
            'sort' => 2,
        ]);
        $oBanner->save();
        $iBanner = $oBanner->id;

        $aBanners = Banners::getBanners($iSection1, [], 'left');

        $this->assertInternalType('array', $aBanners);
        $this->assertEquals(count($aBanners), 1);

        $this->assertEquals($aBanners[0]['id'], $iBanner);

        $aBanners = Banners::getBanners($iSection2, [], 'right');
        $this->assertEquals(count($aBanners), 0);

        /*Баннер тянем от родительского*/
        $oBanner = Banners::getBlankBanner();
        $oBanner->setAttributes([
            'section' => $iSection1,
            'on_main' => 1,
            'on_allpages' => 0,
            'on_include' => 1,
            'sort' => 1,
        ]);
        $oBanner->save();
        $iBanner = $oBanner->id;

        $aBanners = Banners::getBanners($iSection2, [$iSection1], 'left');

        $this->assertInternalType('array', $aBanners);
        $this->assertEquals(count($aBanners), 2);

        $this->assertEquals($aBanners[0]['id'], $iBanner);

        $aBanners = Banners::getBanners($iSection2, [], 'right');
        $this->assertEquals(count($aBanners), 0);

        $aBanners = Banners::getBanners($iSection3, [$iSection1], 'left');

        $this->assertInternalType('array', $aBanners);
        $this->assertEquals(count($aBanners), 2);

        $this->assertEquals($aBanners[0]['id'], $iBanner);

        $aBanners = Banners::getBanners($iSection3, [], 'right');
        $this->assertEquals(count($aBanners), 0);

        /*Баннер тянем для всех страниц*/
        $oBanner = Banners::getBlankBanner();
        $oBanner->setAttributes([
            'section' => $iSection1,
            'on_main' => 0,
            'on_allpages' => 1,
            'on_include' => 0,
            'sort' => 3,
        ]);
        $oBanner->save();
        $iBanner = $oBanner->id;

        $aBanners = Banners::getBanners($iSection2, [$iSection1], 'left');

        $this->assertInternalType('array', $aBanners);
        $this->assertEquals(count($aBanners), 3);

        $this->assertEquals($aBanners[2]['id'], $iBanner);

        $aBanners = Banners::getBanners($iSection2, [], 'right');
        $this->assertEquals(count($aBanners), 0);
    }
}
