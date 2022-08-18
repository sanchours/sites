<?php

namespace unit\build\Adm\Tree;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Page;
use skewer\base\section\Tree;
use skewer\components\search\models\SearchIndex;
use unit\data\TestHelper;

/**
 * Class SearchTest.
 *
 * @group search
 */
class SearchTest extends \Codeception\Test\Unit
{
    /** @covers \skewer\build\Adm\Tree\Search::checkSection */
    public function testCheckLanguageSection()
    {
        $oMock = $this->getSimpleMock();

        $aLanguageSections = \Yii::$app->sections->getValues(Page::LANG_ROOT);

        $oSection = new TreeSection();
        $oSection->id = reset($aLanguageSections);

        TestHelper::setClosedProperty($oMock, 'oSection', $oSection);
        $res = TestHelper::callPrivateMethod($oMock, 'checkSection');

        $this->assertFalse($res, 'корневой раздел языковой версии прошёл проверку');
    }

    /**
     * @covers \skewer\build\Adm\Tree\Search::beforeUpdate()
     */
    public function testBeforeUpdateWithSectionRecursive()
    {
        $s1 = new TreeSection();
        $s1->id = 123;
        $s1->parent = \Yii::$app->sections->leftMenu();
        $s1->title = 'test';

        $oSearchIndex = new SearchIndex();
        $oSearchIndex->object_id = 123;

        $oMock = $this
            ->getMockBuilder('\skewer\build\Adm\Tree\Search')
            ->setMethods(['resetSectionRecursive'])
            ->getMock();

        $oMock->setRecursiveResetFlag(true);

        $oMock
            ->expects($this->once())
            ->method('resetSectionRecursive')
            ->with($s1->id);

        TestHelper::setClosedProperty($oMock, 'bRecursiveReset', true);
        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oSearchIndex);
        TestHelper::setClosedProperty($oMock, 'oEntity', $s1);
        TestHelper::callPrivateMethod($oMock, 'beforeUpdate');
    }

    /**
     * @covers \skewer\build\Adm\Tree\Search::beforeUpdate
     */
    public function testBeforeUpdateResetSectionAndEntitiesByTemplate()
    {
        $s1 = new TreeSection();
        $s1->id = 123;
        $s1->parent = \Yii::$app->sections->templates();
        $s1->title = 'test';

        $oMock = $this
            ->getMockBuilder('\skewer\build\Adm\Tree\Search')
            ->setMethods(['resetSectionAndEntitiesByTemplate'])
            ->getMock();

        $oMock
            ->expects($this->once())
            ->method('resetSectionAndEntitiesByTemplate')
            ->with($s1->id);

        TestHelper::setClosedProperty($oMock, 'oEntity', $s1);
        TestHelper::callPrivateMethod($oMock, 'beforeUpdate');
    }

    /** @covers \skewer\build\Adm\Tree\Search::checkSection */
    public function testCheckTemplateSection()
    {
        $s1 = Tree::addSection(\Yii::$app->sections->templates(), 'test');

        $oMock = $this->getSimpleMock();

        TestHelper::setClosedProperty($oMock, 'oSection', $s1);
        $res = TestHelper::callPrivateMethod($oMock, 'checkSection');

        $this->assertFalse($res, 'раздел-шаблон прошёл проверку');

        Tree::removeSection($s1->id);
    }

    /** @covers \skewer\build\Adm\Tree\Search::checkSection */
    public function testCheckSection()
    {
        $s1 = Tree::addSection(\Yii::$app->sections->leftMenu(), 'test');

        $oMock = $this->getSimpleMock();

        TestHelper::setClosedProperty($oMock, 'oSection', $s1);
        $res = TestHelper::callPrivateMethod($oMock, 'checkSection');

        $this->assertTrue($res, 'раздел не прошел проверку');

        Tree::removeSection($s1->id);
    }

    /** @covers \skewer\build\Adm\Tree\Search::grabEntity */
    public function testGrabEntity()
    {
        $oMock = $this->getSimpleMock();

        $oTestSection = Tree::addSection(\Yii::$app->sections->leftMenu(), 'testio');

        $oSearchRow = new SearchIndex();
        $oSearchRow->object_id = $oTestSection->id;

        TestHelper::setClosedProperty($oMock, 'oSearchIndexRow', $oSearchRow);

        $res = TestHelper::callPrivateMethod($oMock, 'grabEntity');
        $this->assertEquals($res->attributes, $oTestSection->attributes);

        Tree::removeSection($oTestSection->id);
    }

    /** @covers \skewer\build\Adm\Tree\Search::checkEntity */
    public function testCheckEntity()
    {
        $oMock = $this->getSimpleMock();

        $res = TestHelper::callPrivateMethod($oMock, 'checkEntity');
        $this->assertTrue($res);
    }

    /** Вернёт double-объект с НЕ перекрытыми методами */
    private function getSimpleMock()
    {
        return $this
            ->getMockBuilder('\skewer\build\Adm\Tree\Search')
            ->getMock();
    }
}
