<?php

namespace unit\build\Catalog\Goods;

use skewer\components\catalog\Card;
use skewer\components\catalog\Generator;
use skewer\components\catalog\GoodsRow;
use unit\data\TestHelper;

/**
 * @group search
 * Class SearchTest
 */
class SearchTest extends \Codeception\Test\Unit
{
    const ext_card_name = 'test_ext_card';

    public function setUp()
    {
        Generator::createExtCard(Card::get(Card::DEF_BASE_CARD)->id, self::ext_card_name);
    }

    public function tearDown()
    {
        $oExtCard = Card::get(self::ext_card_name);
        $oExtCard->delete();
    }

    /**
     * @cover \skewer\build\Catalog\Goods\Search::grabEntity
     */
    public function testGrabEntity()
    {
        $oMock = $this
            ->getMockBuilder('\skewer\build\Catalog\Goods\Search')
            ->setMethods(['grabEntityFromDb'])
            ->getMock();

        $std1 = new \stdClass();
        $std1->name = 'std1';
        $oMock->expects($this->once())->method('grabEntityFromDb')->willReturn($std1);

        TestHelper::setClosedProperty($oMock, 'oEntity', null);
        $this->assertEquals(TestHelper::callPrivateMethod($oMock, 'grabEntity'), $std1);
    }

    /**
     * @cover \skewer\build\Catalog\Goods\Search::grabEntity
     */
    public function testGrabEntity2()
    {
        $oMock = $this
            ->getMockBuilder('\skewer\build\Catalog\Goods\Search')
            ->setMethods(['grabEntityFromDb'])
            ->getMock();

        $oMock->expects($this->never())->method('grabEntityFromDb');

        $std = new \stdClass();
        $std->name = 'std';
        TestHelper::setClosedProperty($oMock, 'oEntity', $std);
        $res = TestHelper::callPrivateMethod($oMock, 'grabEntity');
        $this->assertEquals($res, $std);
    }

    /** @covers \skewer\build\Catalog\Goods\Search::checkEntity */
    public function testCheckEntity()
    {
        $oMock = $this
            ->getMockBuilder('\skewer\build\Catalog\Goods\Search')
            ->getMock();

        TestHelper::setClosedProperty($oMock, 'oEntity', null);
        $res = TestHelper::callPrivateMethod($oMock, 'checkEntity');
        $this->assertFalse($res, 'проверка пройдена с пустой сущностью');

        $oGood = GoodsRow::create(self::ext_card_name);

        // не активный товар
        $oGood->getBaseRow()->setVal('active', 0);
        TestHelper::setClosedProperty($oMock, 'oEntity', $oGood);
        $res = TestHelper::callPrivateMethod($oMock, 'checkEntity');
        $this->assertFalse($res, 'не активный товар прошел проверку');

        // активный товар
        $oGood->getBaseRow()->setVal('active', 1);
        TestHelper::setClosedProperty($oMock, 'oEntity', $oGood);
        TestHelper::setClosedProperty($oMock, 'bIsGoodsModificationEnable', true);
        $res = TestHelper::callPrivateMethod($oMock, 'checkEntity');
        $this->assertTrue($res, 'активный товар не прошел проверку');
    }

    /**
     * @covers \skewer\build\Catalog\Goods\Search::checkEntity */
    public function testCheckEntityWithModification()
    {
        $oMock = $this
            ->getMockBuilder('\skewer\build\Catalog\Goods\Search')
            ->setMethods(['isModificationGood'])
            ->getMock();

        $oMock
            ->expects($this->once())
            ->method('isModificationGood')
            ->willReturn(true);

        $oGood = GoodsRow::create(self::ext_card_name);
        $oGood->getBaseRow()->setVal('active', 1);

        TestHelper::setClosedProperty($oMock, 'oEntity', $oGood);
        TestHelper::setClosedProperty($oMock, 'bIsGoodsModificationEnable', false);

        $res = TestHelper::callPrivateMethod($oMock, 'checkEntity');
        $this->assertFalse($res, 'товар-модификация при отключенных модификациях прошел проверку');
    }

    /** @covers \skewer\build\Catalog\Goods\Search::fillSearchRow */
    public function testFillSearchRow()
    {
    }
}
