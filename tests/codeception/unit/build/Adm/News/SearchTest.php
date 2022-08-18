<?php

namespace unit\build\Adm\News;

use skewer\build\Adm\News\models\News;
use skewer\components\search\models\SearchIndex;
use unit\data\TestHelper;

/**
 * Class SearchTest.
 *
 * @group search
 */
class SearchTest extends \Codeception\Test\Unit
{
    /** @covers \skewer\build\Adm\News\Search::grabEntity */
    public function testGrabEntity()
    {
        $oNews = new News();
        $oNews->title = 'test news';
        $oNews->parent_section = 123;

        $this->assertTrue($oNews->save(), 'новость не сохранена');

        $oSearchRow = new SearchIndex();
        $oSearchRow->object_id = $oNews->id;

        $oMock = $this->getSimpleMock();

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oSearchRow);
        $this->assertNotEmpty(TestHelper::callPrivateMethod($oMock, 'grabEntity'), 'не нашли новость');

        $oSearchRow = new SearchIndex();
        $oSearchRow->object_id = 99999;

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oSearchRow);
        $this->assertEmpty(TestHelper::callPrivateMethod($oMock, 'grabEntity'), 'выбрана не существующая новость');

        $oNews->delete();
    }

    /**
     * @covers \skewer\build\Adm\News\Search::checkEntity
     * @dataProvider newsProvider
     *
     * @param mixed $in
     * @param mixed $out
     * @param mixed $error
     * */
    public function testCheckEntity($in, $out, $error)
    {
        $oMock = $this->getSimpleMock();

        TestHelper::setClosedProperty($oMock, 'oEntity', $in);
        $res1 = TestHelper::callPrivateMethod($oMock, 'checkEntity');
        $this->assertEquals($res1, $out, $error);
    }

    public function newsProvider()
    {
        return [
            [null, false, 'не заданная сущность прошла проверку'],
            [new News(['full_text' => '']), false, 'новость без текста прошла проверку'],
            [new News(['active' => 0]), false, 'не активная новость прошла проверку'],
            [new News(['full_text' => 'text', 'active' => 1]), true, 'коррректная новость не прошла проверку'],
        ];
    }

    /** @covers \skewer\build\Adm\News\Search::getNewSectionId */
    public function testGetNewSectionId()
    {
        $oMock = $this->getSimpleMock();

        $oEntity = new News();
        $oEntity->parent_section = 1;

        TestHelper::setClosedProperty($oMock, 'oEntity', $oEntity);
        $this->assertEquals($oEntity->parent_section, TestHelper::callPrivateMethod($oMock, 'getNewSectionId'));

        $oEntity->parent_section = 2;
        TestHelper::setClosedProperty($oMock, 'oEntity', $oEntity);
        $this->assertEquals($oEntity->parent_section, TestHelper::callPrivateMethod($oMock, 'getNewSectionId'));
    }

    private function getSimpleMock()
    {
        return $this
            ->getMockBuilder('\skewer\build\Adm\News\Search')
            ->getMock();
    }
}
