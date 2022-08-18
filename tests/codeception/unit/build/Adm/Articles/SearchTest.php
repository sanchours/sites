<?php

namespace unit\build\Adm\Articles;

use skewer\build\Page\Articles\Model\Articles;
use skewer\build\Page\Articles\Model\ArticlesRow;
use skewer\components\search\models\SearchIndex;
use unit\data\TestHelper;

/**
 * Class SearchTest.
 *
 * @group search
 */
class SearchTest extends \Codeception\Test\Unit
{
    /** @covers \skewer\build\Adm\Articles\Search::grabEntity */
    public function testGrabEntity()
    {
        $oArticle = new ArticlesRow();
        $oArticle->title = 'test articles';
        $oArticle->parent_section = 123;

        $this->assertTrue((bool) $oArticle->save(), 'статья не сохранена');

        $oSearchRow = new SearchIndex();
        $oSearchRow->object_id = $oArticle->id;

        $oMock = $this->getSimpleMock();

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oSearchRow);
        $this->assertNotEmpty(TestHelper::callPrivateMethod($oMock, 'grabEntity'), 'не нашли новость');

        $oSearchRow = new SearchIndex();
        $oSearchRow->object_id = 99999;

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oSearchRow);
        $this->assertEmpty(TestHelper::callPrivateMethod($oMock, 'grabEntity'), 'выбрана не существующая статья');

        $oArticle->delete();
    }

    /**
     * @dataProvider articlesProvider
     * @covers \skewer\build\Adm\Articles\Search::checkEntity
     *
     * @param mixed $in
     * @param mixed $out
     * @param mixed $error
     */
    public function testCheckEntity($in, $out, $error)
    {
        $oMock = $this->getSimpleMock();

        TestHelper::setClosedProperty($oMock, 'oEntity', $in);
        $res1 = TestHelper::callPrivateMethod($oMock, 'checkEntity');
        $this->assertEquals($res1, $out, $error);
    }

    public function articlesProvider()
    {
        return [
            [null, false, 'не заданная сущность прошла проверку'],
            [Articles::getNewRow(['full_text' => '']), false, 'статья без текста прошла проверку'],
            [Articles::getNewRow(['active' => 0]), false, 'не активная статья прошла проверку'],
            [Articles::getNewRow(['full_text' => 'qwe', 'active' => 1]), true, 'коррректная статья не прошла проверку'],
        ];
    }

    /** @covers \skewer\build\Adm\Articles\Search::getNewSectionId */
    public function testGetNewSectionId()
    {
        $oMock = $this->getSimpleMock();

        $oEntity = new ArticlesRow();
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
            ->getMockBuilder('\skewer\build\Adm\Articles\Search')
            ->getMock();
    }
}
