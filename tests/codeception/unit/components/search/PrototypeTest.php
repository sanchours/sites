<?php

namespace unit\components\search;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\components\search\models\SearchIndex;
use skewer\components\search\Prototype;
use unit\data\TestHelper;

/**
 * Class PrototypeTest.
 *
 * @group search
 */
class PrototypeTest extends \Codeception\Test\Unit
{
    const search_class_name = 'test';

    const test_object_id = 55;

    public function tearDown()
    {
        SearchIndex::deleteAll(['class_name' => self::search_class_name]);
    }

    public function stripProvider()
    {
        return [
            [
                '<p>qwe</p>',
                'qwe',
            ],
            [
                '<style>123</style>qwe',
                'qwe',
            ],
            [
                '<script>alert(123);</script>qwe',
                'qwe',
            ],
            [
                '<script>alert(123);</script><p>qwe<p>',
                'qwe',
            ],
            [
<<<IN
<script>
     alert(123);
     alert(123);
     alert(123);
</script>
hello
<style>
    .p {
        color: red;
    }
</style>
<script>
     alert(123);
     alert(123);
     alert(123);
</script>
<div>
    <p>qwe<p>
</div>
IN
                     ,
<<<OUT

hello

    qwe

OUT
            ],
        ];
    }

    /**
     * @covers \skewer\components\search\Prototype::stripTags
     * @dataProvider stripProvider
     *
     * @param string $in
     * @param string $out
     */
    public function testStripTags($in, $out)
    {
        $o = new TestSearchEngine();

        $this->assertSame($out, $o->getStrip($in));
    }

    /**
     * Тест обновления записи с пустым object_id.
     *
     * @covers \Prototype::updateByObjectId()
     */
    public function testUpdateRecordWithZeroObjectId()
    {
        $oSearch = $this
            ->getMockBuilder('\skewer\components\search\Prototype')
            ->setMethods(['getExistOrNewRecord', 'deleteToObjectId'])
            ->getMockForAbstractClass();

        $oSearchIndex = new SearchIndex();
        $oSearchIndex->object_id = 0;

        $oSearch->expects($this->once())->method('getExistOrNewRecord')->willReturn($oSearchIndex);
        $oSearch->expects($this->once())->method('deleteToObjectId')->with($oSearchIndex->object_id);

        $res = TestHelper::callPrivateMethod($oSearch, 'updateByObjectId', [1]);
        $this->assertTrue($res);
    }

    /**
     * Тест обновления поисковой записи в случае отсутствия сущности.
     *
     * @covers \Prototype::updateByObjectId()
     */
    public function testUpdateByObjectIdWithEmptyEntity()
    {
        $created1 = new SearchIndex();
        $created1->search_title = 's';
        $created1->search_text = 's';
        $created1->class_name = self::search_class_name;
        $created1->object_id = self::test_object_id;
        $created1->use_in_search = 1;
        $created1->use_in_sitemap = 1;
        $created1->status = 0;

        $oMock = $this
            ->getMockBuilder('\skewer\components\search\Prototype')
            ->setMethods(['checkRow', 'getExistOrNewRecord', 'grabEntity', 'deleteToObjectId']) //перекрываем конкретный метод, абстрактные перекроются сами
            ->getMockForAbstractClass();

        $oMock->expects($this->once())->method('getExistOrNewRecord')->willReturn($created1);
        $oMock->expects($this->once())->method('grabEntity')->willReturn(false);
        $oMock->expects($this->once())->method('deleteToObjectId');

        $res = TestHelper::callPrivateMethod($oMock, 'updateByObjectId', [$created1->object_id]);

        $this->assertTrue($res);
    }

    /**
     * Тест проверки поиск. записи с завалившей проверку сущностью.
     *
     * @covers \Prototype::checkRow()
     */
    public function testCheckRowRowWithFailedVerificationEntity()
    {
        $oCorrectSearchRow = new SearchIndex();
        $oCorrectSearchRow->object_id = 123;

        $oMock = $this
            ->getMockBuilder('\skewer\components\search\Prototype')
            ->setMethods(['getNewSectionId', 'checkSection']) //перекрытие конкретных(не абстрактных) методов
            ->getMockForAbstractClass();

        $oMock->expects($this->once())->method('checkEntity')->willReturn(false);
        $oMock->expects($this->never())->method('getNewSectionId');

        $this->assertFalse(TestHelper::callPrivateMethod($oMock, 'checkRow', [$oCorrectSearchRow]), 'сущность ошибочно прошла проверку');
    }

    /**
     * Тест проверки поиск. записи с завалившим проверку разделом
     *
     * @covers \Prototype::checkRow()
     */
    public function testCheckRowWithFailedVerificationSection()
    {
        $oCorrectSearchRow = new SearchIndex();
        $oCorrectSearchRow->object_id = 123;

        $oMock = $this
            ->getMockBuilder('\skewer\components\search\Prototype')
            ->setMethods(['getNewSectionId', 'checkSection']) //перекрытие конкретных(не абстрактных) методов
            ->getMockForAbstractClass();

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oCorrectSearchRow);

        $oMock->expects($this->once())->method('checkEntity')->willReturn(true);
        $oMock->expects($this->once())->method('getNewSectionId')->willReturn(123);
        $oMock->expects($this->once())->method('checkSection')->willReturn(false);
        $oMock->expects($this->never())->method('fillSearchRow');

        $this->assertFalse(TestHelper::callPrivateMethod($oMock, 'checkRow'), 'раздел ошибочно прошел проверку');
    }

    /**
     * Тест проверяет последовательность вызовов.
     *
     * @covers \Prototype::checkRow()
     */
    public function testCheckRowWayByCondition()
    {
        $oCorrectSearchRow = new SearchIndex();
        $oCorrectSearchRow->object_id = 123;

        $oMock = $this
            ->getMockBuilder('\skewer\components\search\Prototype')
            ->setMethods(['getNewSectionId', 'checkSection']) //перекрытие конкретных(не абстрактных) методов
            ->getMockForAbstractClass();

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oCorrectSearchRow);

        $oMock->expects($this->once())->method('checkEntity')->willReturn(true);
        $oMock->expects($this->once())->method('getNewSectionId')->willReturn(123);
        $oMock->expects($this->once())->method('checkSection')->willReturn(true);

        $this->assertTrue(TestHelper::callPrivateMethod($oMock, 'checkRow'), 'поисковая запись не обновлена');
    }

    /**
     * Проверка получения существующего раздела.
     *
     * @covers \skewer\components\search\Prototype::grabSection
     */
    public function testGrabSection()
    {
        $s1 = Tree::addSection(\Yii::$app->sections->leftMenu(), 'test1');

        $oCorrectSearchRow = new SearchIndex();
        $oCorrectSearchRow->section_id = $s1->id;

        $oMock = $this->getMock4SearchPrototype();

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oCorrectSearchRow);
        $res = TestHelper::callPrivateMethod($oMock, 'grabSection');

        $this->assertInstanceOf('\skewer\base\section\models\TreeSection', $res);

        Tree::removeSection($s1->id);
    }

    /**
     * Проверка получения несуществующего раздела.
     *
     * @covers \skewer\components\search\Prototype::grabSection
     */
    public function testGrabSection2()
    {
        $oCorrectSearchRow = new SearchIndex();
        $oCorrectSearchRow->section_id = 99999;

        $oMock = $this->getMock4SearchPrototype();

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oCorrectSearchRow);
        $res = TestHelper::callPrivateMethod($oMock, 'grabSection');

        $this->assertNull($res);
    }

    /**
     * @covers \skewer\components\search\Prototype::checkSection
     * @dataProvider providerSection4CheckSection
     *
     * @param mixed $oSection
     * @param mixed $bRes
     */
    public function testCheckSection($oSection, $bRes)
    {
        $oMock = $this->getMock4SearchPrototype();

        TestHelper::setClosedProperty($oMock, 'oSection', $oSection);
        $this->assertEquals($bRes, TestHelper::callPrivateMethod($oMock, 'checkSection'));
    }

    public function providerSection4CheckSection()
    {
        return [
            [null, false],
            [new TreeSection(['visible' => Visible::HIDDEN_FROM_PATH]), false],
            [new TreeSection(['visible' => Visible::HIDDEN_NO_INDEX]), false],
            [new TreeSection(['link' => 'test']), false],
            [new TreeSection(['visible' => Visible::VISIBLE]), true],
            [new TreeSection(['visible' => Visible::HIDDEN_FROM_MENU]), true],
        ];
    }

    /**
     * @covers \skewer\components\search\Prototype::getNewSectionId
     */
    public function testGetNewSectionId()
    {
        $oMock = $this->getMock4SearchPrototype();

        $rand = random_int(1, 1000);

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', new SearchIndex(['section_id' => $rand]));
        $res = TestHelper::callPrivateMethod($oMock, 'getNewSectionId');
        $this->assertEquals($rand, $res);
    }

    /** Получить мок для абстрактного класса \skewer\components\search\Prototype */
    private function getMock4SearchPrototype()
    {
        return $this
            ->getMockBuilder('\skewer\components\search\Prototype')
            ->getMockForAbstractClass();
    }
}

class TestSearchEngine extends Prototype
{
    protected function grabEntity()
    {
    }

    protected function checkEntity()
    {
    }

    protected function fillSearchRow()
    {
    }

    /**
     * отдает имя идентификатора ресурса для работы с поисковым индексом
     *
     * @return string
     */
    public function getName()
    {
        return 'TestSearchEngine';
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRow()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function restore()
    {
    }

    public function getStrip($sText)
    {
        return $this->stripTags($sText);
    }

    protected function buildHrefSearchIndexRow()
    {
        return '';
    }
}
