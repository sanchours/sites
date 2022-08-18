<?php

namespace unit\build\Adm\FAQ;

use skewer\build\Adm\FAQ\models\Faq;
use skewer\components\search\models\SearchIndex;
use unit\data\TestHelper;

/**
 * Class SearchTest.
 *
 * @group search
 */
class SearchTest extends \Codeception\Test\Unit
{
    /** @covers \skewer\build\Adm\FAQ\Search::grabEntity */
    public function testGrabEntity()
    {
        $oFaq = new Faq();
        $oFaq->parent = 123;
        $oFaq->status = Faq::statusApproved;
        $oFaq->content = 'test faq';

        $this->assertTrue((bool) $oFaq->save(), 'вопрос не сохранен');

        $oSearchRow = new SearchIndex();
        $oSearchRow->object_id = $oFaq->id;

        $oMock = $this->getSimpleMock();

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oSearchRow);
        $this->assertNotEmpty(TestHelper::callPrivateMethod($oMock, 'grabEntity'), 'не нашли вопрос');

        $oSearchRow = new SearchIndex();
        $oSearchRow->object_id = 99999;

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oSearchRow);
        $this->assertEmpty(TestHelper::callPrivateMethod($oMock, 'grabEntity'), 'выбрана не существующий вопрос');

        $oFaq->delete();
    }

    /**
     * @dataProvider faqProvider
     * @covers \skewer\build\Adm\FAQ\Search::checkEntity
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

    public function faqProvider()
    {
        return [
            [null, false, 'не заданная сущность прошла проверку'],
            [new Faq(['content' => '']), false, 'вопрос без текста прошёл проверку'],
            [new Faq(['status' => Faq::statusNew]), false, 'не подтвёржденный вопрос прошёл проверку'],
            [new Faq(['status' => Faq::statusApproved, 'content' => 'test']), true, 'коррректный вопрос не прошёл проверку'],
        ];
    }

    /** @covers \skewer\build\Adm\FAQ\Search::getNewSectionId */
    public function testGetNewSectionId()
    {
        $oMock = $this->getSimpleMock();

        $oEntity = new Faq();
        $oEntity->parent = 1;

        TestHelper::setClosedProperty($oMock, 'oEntity', $oEntity);
        $this->assertEquals($oEntity->parent, TestHelper::callPrivateMethod($oMock, 'getNewSectionId'));

        $oEntity->parent = 2;
        TestHelper::setClosedProperty($oMock, 'oEntity', $oEntity);
        $this->assertEquals($oEntity->parent, TestHelper::callPrivateMethod($oMock, 'getNewSectionId'));
    }

    private function getSimpleMock()
    {
        return $this
            ->getMockBuilder('\skewer\build\Adm\FAQ\Search')
            ->getMock();
    }
}
