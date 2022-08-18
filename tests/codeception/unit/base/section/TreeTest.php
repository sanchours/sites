<?php

namespace unit\base\section;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\components\auth\CurrentUser;
use skewer\components\auth\Policy;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * @covers \skewer\base\section\models\TreeSection
 */
class TreeTest extends \Codeception\Test\Unit
{
    protected function setUp()
    {
        Policy::incPolicyVersion();
        Policy::updateCache(CurrentUser::getPolicyId());
        CurrentUser::reloadPolicy();
        Tree::dropCache();
    }

    protected function setDown()
    {
    }

    protected function createTree()
    {
        $a = Tree::addSection(\Yii::$app->sections->root(), 'a');
        $b1 = Tree::addSection($a->id, 'b1');
        Tree::addSection($a->id, 'b2');
        Tree::addSection($b1->id, 'c1');
        $c2 = Tree::addSection($b1->id, 'c2');
        Tree::addSection($c2->id, 'd');

        return $a->id;
    }

    private function createTestSections()
    {
        $t11 = Tree::addSection(\Yii::$app->sections->root(), 'test1-1');
        $t21 = Tree::addSection($t11->id, 'test2-1');
        $t22 = Tree::addSection($t11->id, 'test2-2');
        $t31 = Tree::addSection($t21->id, 'test3-1');
        $t32 = Tree::addSection($t21->id, 'test3-2');
        $t33 = Tree::addSection($t22->id, 'test3-3');

        return [
            't11' => $t11->id,
            't21' => $t21->id,
            't22' => $t22->id,
            't31' => $t31->id,
            't32' => $t32->id,
            't33' => $t33->id,
        ];
    }

    public static function getFixtureTree()
    {
        return [
            'visible_sec' => [
                'title' => 'видимый раздел',
                'visible' => Visible::VISIBLE,
                'link' => '',
            ],
            'hidden_from_menu_sec' => [
                'title' => 'скрытый из меню',
                'visible' => Visible::HIDDEN_FROM_MENU,
                'link' => '',
            ],
            'hidden_from_path_sec' => [
                'title' => 'скрытый из пути',
                'visible' => Visible::HIDDEN_FROM_PATH,
                'link' => '',
            ],
            'hidden_no_index' => [
                'title' => 'скрытый от индексации',
                'visible' => Visible::HIDDEN_NO_INDEX,
                'link' => '',
            ],
            'link_sec' => [
                'title' => 'раздел-ссылка',
                'visible' => Visible::VISIBLE,
                'link' => '[111]',
            ],
        ];
    }

    public function getSectionFromFixture($sAlias)
    {
        $aFixtureData = self::getFixtureTree();

        if (!array_key_exists($sAlias, $aFixtureData)) {
            return;
        }

        return TreeSection::findOne(['title' => $aFixtureData[$sAlias]['title']]);
    }

    /**
     * Создаст дерево разделов с всеми типами видимости.
     *
     * @return int - ид ветки раздела
     */
    protected function createTreeWithDifferentVisibilityType()
    {
        $aTmp = self::getFixtureTree();

        $oParentSection = Tree::addSection(\Yii::$app->sections->root(), 'Тестовый раздел');

        foreach ($aTmp as $aAlias => $aSettings) {
            Tree::addSection($oParentSection->id, $aSettings['title'], 7, '', $aSettings['visible'], $aSettings['link']);
        }

        return $oParentSection->id;
    }

    /**
     * @covers \skewer\base\section\Tree::addSection()
     */
    public function testAddSection()
    {
        $sTitle = 'testsection';
        $section = Tree::addSection(\Yii::$app->sections->root(), $sTitle);

        $this->assertNotEmpty($section);
        $this->assertNotEmpty($section->id);

        $realSection = Tree::getSection($section->id);

        $this->assertNotEmpty($realSection);
        $this->assertSame($section->alias, $realSection->alias);
        $this->assertSame($section->parent, $realSection->parent);

        Tree::removeSection($section->id);
    }

    /**
     * @covers \skewer\base\section\Tree::removeSection()
     */
    public function testRemoveSection()
    {
        $t11 = Tree::addSection(\Yii::$app->sections->root(), 'test-remove');

        Tree::removeSection($t11->id);

        $iCountSection = TreeSection::find()
            ->where(['IN', 'id', [$t11->id]])
            ->count();

        $this->assertEquals(0, $iCountSection);
    }

    /**
     * @covers \skewer\base\section\models\TreeSection::delete
     */
    public function testDelete()
    {
        $s1 = Tree::addSection(\Yii::$app->sections->root(), 'sect');
        $s2 = Tree::addSection($s1->id, 'sect');

        $this->assertNotEmpty(TreeSection::findOne($s1->id));
        $this->assertNotEmpty(TreeSection::findOne($s2->id));

        $s1->delete();

        $this->assertEmpty(TreeSection::findOne($s1->id));
        $this->assertEmpty(TreeSection::findOne($s2->id));
    }

    /**
     * @covers \skewer\base\section\Tree::copy()
     */
    public function testCopy()
    {
        $t11 = Tree::addSection(\Yii::$app->sections->root(), 'test000');
        $t21 = Tree::addSection($t11->id, 'test111');

        $oSection = TreeSection::find()
            ->where(['id' => $t11->id])
            ->one();

        Tree::copy($oSection, $t21->id);

        $iCountNewSection = TreeSection::find()
            ->where(['IN', 'parent', [$t21->id]])
            ->count();

        $this->assertEquals(1, $iCountNewSection);

        $t11->delete();
        $t21->delete();
    }

    /**
     * @covers \skewer\base\section\Tree::addSection()
     */
    public function testLevel()
    {
        $q = Tree::addSection(\Yii::$app->sections->topMenu(), 'q');

        $a = Tree::addSection($q->id, 'a');
        $b = Tree::addSection($q->id, 'b');
        $c = Tree::addSection($a->id, 'c');
        $d = Tree::addSection($b->id, 'd');
        $e = Tree::addSection($d->id, 'e');

        $this->assertSame($q->level, 2);
        $this->assertSame($a->level, 3);
        $this->assertSame($b->level, 3);
        $this->assertSame($c->level, 4);
        $this->assertSame($d->level, 4);
        $this->assertSame($e->level, 5);

        $q->delete();
    }

    /**
     * @covers \skewer\base\section\Tree::getSection()
     */
    public function testGetSection()
    {
        $test = Tree::addSection(\Yii::$app->sections->root(), 'Тестовый');

        $section = Tree::getSection($test->id);
        $this->assertSame($section->id, $test->id);
        $this->assertSame($section->title, 'Тестовый');

        $section = Tree::getSection($test->id, true);
        $this->assertInternalType('array', $section);
        $this->assertCount(11, $section);
        $this->assertSame('Тестовый', $section['title']);

        $section = Tree::getSection(123123123);
        $this->assertNull($section);

        Tree::removeSection($test->id);

        $iParentId = $this->createTreeWithDifferentVisibilityType();

        $this->assertSame(
            Tree::getSection($this->getSectionFromFixture('visible_sec')->id)->id,
            $this->getSectionFromFixture('visible_sec')->id
        );

        $this->assertSame(
            Tree::getSection($this->getSectionFromFixture('visible_sec')->id, false, true)->id,
            $this->getSectionFromFixture('visible_sec')->id
        );

        $this->assertSame(
            Tree::getSection($this->getSectionFromFixture('hidden_from_menu_sec')->id)->id,
            $this->getSectionFromFixture('hidden_from_menu_sec')->id
        );

        $this->assertSame(
            Tree::getSection($this->getSectionFromFixture('hidden_from_menu_sec')->id, false, true)->id,
            $this->getSectionFromFixture('hidden_from_menu_sec')->id
        );

        $this->assertSame(
            Tree::getSection($this->getSectionFromFixture('hidden_from_path_sec')->id, false, false)->id,
            $this->getSectionFromFixture('hidden_from_path_sec')->id
        );

        $this->assertSame(
            Tree::getSection($this->getSectionFromFixture('hidden_from_path_sec')->id, false, true),
            null
        );

        $this->assertSame(
            Tree::getSection($this->getSectionFromFixture('hidden_no_index')->id, false, false)->id,
            $this->getSectionFromFixture('hidden_no_index')->id
        );

        $this->assertSame(
            Tree::getSection($this->getSectionFromFixture('hidden_no_index')->id, false, true),
            null
        );

        $this->assertSame(
            Tree::getSection($this->getSectionFromFixture('link_sec')->id, false, false)->id,
            $this->getSectionFromFixture('link_sec')->id
        );

        $this->assertSame(
            Tree::getSection($this->getSectionFromFixture('link_sec')->id, false, true),
            null
        );

        Tree::removeSection($iParentId);
    }

    /**
     * @covers \skewer\base\section\Tree::getSections()
     */
    public function testGetSections()
    {
        $s1 = Tree::addSection(\Yii::$app->sections->root(), 's1');
        $s2 = Tree::addSection(\Yii::$app->sections->root(), 's2');

        $list = Tree::getSections([$s1->id, $s2->id]);

        $this->assertNotEmpty($list);
        $this->assertSame(count($list), 2);
        $this->assertSame($list[$s1->id]->title, 's1');
        $this->assertSame($list[$s2->id]->title, 's2');

        $s1->delete();
        $s2->delete();
    }

    public function testCheckAlias()
    {
        $sTitle = 'testsection';
        $section = Tree::addSection(\Yii::$app->sections->main(), $sTitle);

        $sTitle = 'testsection';
        $wsection = Tree::addSection(\Yii::$app->sections->main(), $sTitle);

        $this->assertNotEmpty($section->alias);
        $this->assertNotEmpty($wsection->alias);
        $this->assertNotSame($section->alias, $wsection->alias);

        $section->delete();
        $wsection->delete();
    }

    /**
     * @covers \skewer\base\section\Tree::getSectionsTitle()
     */
    public function testGetSectionTitle()
    {
        $sTitle = 'testsection';
        $section = Tree::addSection(\Yii::$app->sections->root(), $sTitle);
        Tree::addSection($section->id, 'sub_' . $sTitle);

        $this->assertNotEmpty($section);
        $this->assertSame($sTitle, Tree::getSectionsTitle($section->id));

        $section->delete();
    }

    /**
     * @covers \skewer\base\section\Tree::getSectionTree()
     */
    public function testGetSectionTree()
    {
        $id = $this->createTree();

        $tree = Tree::getSectionTree($id);

        $this->assertSame(count($tree), 2);
        $this->assertSame($tree[0]['title'], 'b1');
        $this->assertSame($tree[1]['title'], 'b2');
        $this->assertSame(count($tree[0]['children']), 2);
        $this->assertSame($tree[0]['children'][0]['title'], 'c1');
        $this->assertSame($tree[0]['children'][1]['title'], 'c2');
        $this->assertSame(count($tree[0]['children'][1]['children']), 1);
        $this->assertSame($tree[0]['children'][1]['children'][0]['title'], 'd');

        Tree::removeSection($id);
    }

    /**
     * @covers \skewer\base\section\Tree::getSectionList()
     */
    public function testGetSectionList()
    {
        $id = $this->createTree();

        $list = Tree::getSectionList($id);

        $this->assertSame(count($list), 6);

        $this->assertSame($list[0]['title'], 'a');
        $this->assertSame($list[1]['title'], '-b1');
        $this->assertSame($list[2]['title'], '--c1');
        $this->assertSame($list[3]['title'], '--c2');
        $this->assertSame($list[4]['title'], '---d');
        $this->assertSame($list[5]['title'], '-b2');

        Tree::removeSection($id);
    }

    public function testAliasPath()
    {
        // init
        $id_a = Tree::addSection(\Yii::$app->sections->root(), 'apa', 0, 'test-section-a', 1);
        $id_b1 = Tree::addSection($id_a->id, 'apb1', 0, 'test-section-b1', 0);
        $id_b2 = Tree::addSection($id_a->id, 'apb2', 0, 'test-section-b2', 1);
        $id_b3 = Tree::addSection($id_a->id, 'apb3', 0, 'test-section-b3', 2);
        $id_c1 = Tree::addSection($id_b1->id, 'apc1', 0, 'test-section-c1', 1);
        $id_d1 = Tree::addSection($id_b2->id, 'apd1', 0, 'test-section-d1', 1);
        Tree::addSection($id_b2->id, 'apd2', 0, 'test-section-d2', 1);
        $id_e1 = Tree::addSection($id_b3->id, 'ape1', 0, 'test-section-e1', 1);

        $test_section = Tree::getSection($id_c1->id);
        $this->assertSame($test_section->alias_path, '/test-section-a/test-section-b1/test-section-c1/');

        // change alias path
        $section = Tree::getSection($id_a->id);
        $section->alias = 'test-section-new';
        $section->save();

        // test
        $test_section = Tree::getSection($id_b1->id);
        $this->assertSame($test_section->alias_path, '/test-section-new/test-section-b1/');
        $test_section = Tree::getSection($id_c1->id);
        $this->assertSame($test_section->alias_path, '/test-section-new/test-section-b1/test-section-c1/');
        $test_section = Tree::getSection($id_d1->id);
        $this->assertSame($test_section->alias_path, '/test-section-new/test-section-b2/test-section-d1/');
        $test_section = Tree::getSection($id_e1->id);
        $this->assertSame($test_section->alias_path, '/test-section-new/test-section-e1/');

        Tree::removeSection($id_a);
    }

    /**
     * Проверка перестроения path при изменении родительского раздела.
     *
     * @covers \skewer\base\section\models\TreeSection::save
     * @covers \skewer\components\seo\Service::generateAlias()
     */
    public function testParentChange()
    {
        /**
         * создать 2 раздела
         * созд третий в 1
         * перенести его во 2
         * проверить.
         */

        /**
         * Было
         *  a
         *  |- b1
         *  |   \- c1
         *  |- b2
         *  |   \- d1
         *  \- b3
         *      \- e1.
         *
         * Делаем
         *  a
         *  |- b1
         *  |- b2
         *  |   \- d1
         *  \- b3
         *      |- e1
         *      \- c1
         */

        // init
        $id_a = Tree::addSection(\Yii::$app->sections->root(), 'apa', 0, 'test-xsection-a', 1);
        $id_b1 = Tree::addSection($id_a->id, 'apb1', 0, 'test-xsection-b1', 1);
        $id_b2 = Tree::addSection($id_a->id, 'apb2', 0, 'test-xsection-b2', 1);
        $id_b3 = Tree::addSection($id_a->id, 'apb3', 0, 'test-xsection-b3', 1);
        $id_c1 = Tree::addSection($id_b1->id, 'apc1', 0, 'test-xsection-c1', 1);
        Tree::addSection($id_b2->id, 'apd1', 0, 'test-xsection-d1', 1);
        Tree::addSection($id_b2->id, 'apd2', 0, 'test-xsection-d2', 1);
        Tree::addSection($id_b3->id, 'ape1', 0, 'test-xsection-e1', 1);

        // проверяем правильность построения path для переносимого раздела (c1)
        $test_section = Tree::getSection($id_c1->id);
        $this->assertSame('/test-xsection-a/test-xsection-b1/test-xsection-c1/', $test_section->alias_path);

        // проверяем правильность построения path для приемника (b3)
        $new_root = Tree::getSection($id_b3->id);
        $this->assertSame('/test-xsection-a/test-xsection-b3/', $new_root->alias_path);

        $test_section->parent = $new_root->id;
        $test_section->save();

        // test
        $test_section = Tree::getSection($id_c1->id);
        $this->assertSame('/test-xsection-a/test-xsection-b3/test-xsection-c1/', $test_section->alias_path);

        Tree::removeSection($id_a);
    }

    /**
     * \skewer\base\section\models\TreeSection::changePosition.
     */
    public function testChangePosition()
    {
        $a = Tree::addSection(\Yii::$app->sections->root(), 'a', 0, 'w-test-section-a');
        $b = Tree::addSection($a->id, 'b', 0, 'w-test-section-b');
        $c = Tree::addSection($b->id, 'c', 0, 'w-test-section-c');
        $d = Tree::addSection($a->id, 'd', 0, 'w-test-section-d');
        Tree::addSection($d->id, 'e', 0, 'w-test-section-e');
        $f = Tree::addSection($d->id, 'f', 0, 'w-test-section-f');

        $list = Tree::getSectionList($a->id);

        $this->assertSame(count($list), 6);

        $this->assertSame($list[0]['title'], 'a');
        $this->assertSame($list[1]['title'], '-b');
        $this->assertSame($list[2]['title'], '--c');
        $this->assertSame($list[3]['title'], '-d');
        $this->assertSame($list[4]['title'], '--e');
        $this->assertSame($list[5]['title'], '--f');

        $d->changePosition($c, 'before');

        $list = Tree::getSectionList($a->id);

        $this->assertSame(count($list), 6);

        $this->assertSame($list[0]['title'], 'a');
        $this->assertSame($list[1]['title'], '-b');
        $this->assertSame($list[2]['title'], '--d');
        $this->assertSame($list[3]['title'], '---e');
        $this->assertSame($list[4]['title'], '---f');
        $this->assertSame($list[5]['title'], '--c');

        $test_section = Tree::getSection($d->id);
        $this->assertSame($test_section->alias_path, '/w-test-section-a/w-test-section-b/w-test-section-d/');
        $test_section = Tree::getSection($f->id);
        $this->assertSame($test_section->alias_path, '/w-test-section-a/w-test-section-b/w-test-section-d/w-test-section-f/');

        Tree::removeSection($a->id);
    }

    /**
     * @covers \skewer\base\section\Tree::addSection()
     */
    public function testPosition()
    {
        $q = Tree::addSection(\Yii::$app->sections->root(), 'q');
        $a = Tree::addSection($q->id, 'a');
        $b = Tree::addSection($q->id, 'b');
        $c = Tree::addSection($q->id, 'c');
        $d = Tree::addSection($q->id, 'd');
        $e = Tree::addSection($q->id, 'e');

        $this->assertSame($a->position, 1);
        $this->assertSame($b->position, 2);
        $this->assertSame($c->position, 3);
        $this->assertSame($d->position, 4);
        $this->assertSame($e->position, 5);

        $q->delete();
    }

    /**
     * Проверка переназначения позиции при переносе
     * Раздела в другой подраздел (смене родительского раздела).
     *
     * @covers \skewer\base\section\models\TreeSection::save
     */
    public function testPositionChangeOnMove()
    {
        $q1 = Tree::addSection(\Yii::$app->sections->root(), 'q');
        $a = Tree::addSection($q1->id, 'a');
        $b = Tree::addSection($q1->id, 'b');

        $q2 = Tree::addSection(\Yii::$app->sections->root(), 'q');
        $c = Tree::addSection($q2->id, 'c');
        $d = Tree::addSection($q2->id, 'd');
        $e = Tree::addSection($q2->id, 'e');

        $this->assertSame($a->position, 1);
        $this->assertSame($b->position, 2);
        $this->assertSame($c->position, 1);
        $this->assertSame($d->position, 2);
        $this->assertSame($e->position, 3);

        // переносим раздел "а" "q2", индекс доложен быть 3+1=4
        $a->parent = $q2->id;
        $a->save();
        $this->assertSame($a->position, 4, 'не сменился индекс при переносе в другой подраздел');

        // переносим раздел "d" "q1", индекс доложен быть 2+1=3
        $d->parent = $q1->id;
        $d->save();
        $this->assertSame($d->position, 3, 'не сменился индекс при переносе в другой подраздел');

        // переносим раздел "d" обратно в "q2", индекс доложен быть 4+1=5 (должен стать в конец)
        $d->parent = $q2->id;
        $d->save();
        $this->assertSame($d->position, 5, 'не сменился индекс при переносе в другой подраздел');

        $q1->delete();
        $q2->delete();
    }

    public function testShiftPosition()
    {
        $q = Tree::addSection(\Yii::$app->sections->root(), 'q');
        $a = Tree::addSection($q->id, 'a');
        $b = Tree::addSection($q->id, 'b');
        $c = Tree::addSection($q->id, 'c');
        $d = Tree::addSection($q->id, 'd');

        $this->assertSame($a->position, 1);
        $this->assertSame($b->position, 2);
        $this->assertSame($c->position, 3);
        $this->assertSame($d->position, 4);

        $a->changePosition($c, 'after');

        $a->refresh();
        $b->refresh();
        $c->refresh();
        $d->refresh();

        $this->assertSame($a->position, 4);
        $this->assertSame($b->position, 2);
        $this->assertSame($c->position, 3);
        $this->assertSame($d->position, 5);

        $b->changePosition($a, 'before');

        $a->refresh();
        $b->refresh();
        $c->refresh();
        $d->refresh();

        $this->assertSame($a->position, 5);
        $this->assertSame($b->position, 4);
        $this->assertSame($c->position, 3);
        $this->assertSame($d->position, 6);

        $q->delete();
    }

    /**
     * Проверка удаления ресурсов для раздела.
     *
     * @covers \skewer\base\section\models\TreeSection::onSectionDelete
     */
    public function testDeleteForDir()
    {
        $s = Tree::addSection(\Yii::$app->sections->topMenu(), 'sect');

        $sPath = FILEPATH . $s->id . '/';

        $this->assertFileNotExists($sPath);

        mkdir($sPath);
        mkdir($sPath . 'a');
        touch($sPath . 'file1.txt');
        touch($sPath . 'a/file2.txt');

        $this->assertFileExists($sPath);
        $this->assertFileExists($sPath . 'a');
        $this->assertFileExists($sPath . 'file1.txt');
        $this->assertFileExists($sPath . 'a/file2.txt');

        Tree::removeSection($s);

        $this->assertFileNotExists($sPath);
        $this->assertFileNotExists($sPath . 'a');
        $this->assertFileNotExists($sPath . 'file1.txt');
        $this->assertFileNotExists($sPath . 'a/file2.txt');
    }

    /**
     *@covers \skewer\base\section\Tree::getVisibleSections
     */
    public function testGetVisibleSections()
    {
        $a = $this->createTree();

        $aIdeal = TreeSection::find()
            ->where(['visible' => Visible::$aOpenByLink])
            ->andWhere("link LIKE ''")
            ->indexBy('id')
            ->asArray()
            ->column();

        $this->assertEquals($aIdeal, Tree::getVisibleSections(true));

        Tree::removeSection($a);
    }

    /**
     *@covers \skewer\base\section\Tree::getSectionByPath
     */
    public function testGetSectionByPath()
    {
        $aSections = $this->createTestSections();

        $this->assertEquals(0, Tree::getSectionByPath(''));
        $this->assertEquals(Tree::getSectionByPath('/test1-1/test2-2/'), $aSections['t22']);
        $this->assertEquals(Tree::getSectionByPath('/test1-1/'), $aSections['t11']);
        $this->assertEquals(Tree::getSectionByPath('/test_invalid/'), \Yii::$app->sections->main());

        Tree::removeSection($aSections['t11']);
    }

    /**
     *@covers \skewer\base\section\Tree::getSectionByParent
     */
    public function testGetSectionByParent()
    {
        $aSections = $this->createTestSections();

        $aIdeal = TreeSection::find()
            ->where(['IN', 'id', [$aSections['t31'], $aSections['t32']]])
            ->asArray()
            ->all();

        $this->assertEquals($aIdeal, Tree::getSectionByParent($aSections['t21']));

        $falseId = (new Query())->select('MAX(`id`) as max')->from(TreeSection::tableName())->one();
        $this->assertEquals([], Tree::getSectionByParent($falseId['max'] + 1000));

        Tree::removeSection($aSections['t11']);
    }

    /**
     *@covers \skewer\base\section\Tree::getSectionByAlias
     */
    public function testGetSectionByAlias()
    {
        $aSections = $this->createTestSections();

        $aSection = TreeSection::find()
            ->where(['id' => $aSections['t33']])
            ->asArray()
            ->one();

        $this->assertEquals($aSection['id'], Tree::getSectionByAlias($aSection['alias'], $aSection['parent']));
        $this->assertNull(Tree::getSectionByAlias('invalid', $aSection['parent']));
        $falseId = (new Query())->select('MAX(`id`) as max')->from(TreeSection::tableName())->one();
        $this->assertNull(Tree::getSectionByAlias($aSection['alias'], $falseId['max'] + 1000));

        Tree::removeSection($aSections['t11']);
    }

    /**
     *@covers \skewer\base\section\Tree::getSectionParent
     */
    public function testGetSectionParent()
    {
        $aSections = $this->createTestSections();

        $this->assertEquals(Tree::getSectionParent($aSections['t33']), $aSections['t22']);
        $falseId = (new Query())->select('MAX(`id`) as max')->from(TreeSection::tableName())->one();
        $this->assertEquals(Tree::getSectionParent($falseId['max'] + 1000), 0);

        Tree::removeSection($aSections['t11']);
    }

    /**
     *@covers \skewer\base\section\Tree::getSectionAliasPath
     */
    public function testGetSectionAliasPath()
    {
        $t11 = Tree::addSection(\Yii::$app->sections->root(), 'test777');
        $t21 = Tree::addSection($t11->id, 'test888');

        $this->assertEquals(Tree::getSectionAliasPath($t21->id), '/test777/test888/');
        $falseId = (new Query())->select('MAX(`id`) as max')->from(TreeSection::tableName())->one();
        $this->assertEquals(Tree::getSectionAliasPath($falseId['max'] + 1000), '');

        $t11->delete();
    }

    /**
     *@covers \skewer\base\section\Tree::getChainSectionsToCurrentPage
     */
    public function testGetChainSectionsToCurrentPage()
    {
        $aSections = $this->createTestSections();

        $this->assertEquals(Tree::getChainSectionsToCurrentPage($aSections['t33']), 'test1-1/test2-2/test3-3');
        $this->assertEquals(Tree::getChainSectionsToCurrentPage($aSections['t33'], false), 'test1-1/test2-2');
        $falseId = (new Query())->select('MAX(`id`) as max')->from(TreeSection::tableName())->one();
        $this->assertEquals(Tree::getChainSectionsToCurrentPage($falseId['max'] + 1000, false), '');
        $this->assertEquals(Tree::getChainSectionsToCurrentPage($aSections['t33'], false, '---'), 'test1-1---test2-2');
        $this->assertEquals(Tree::getChainSectionsToCurrentPage($aSections['t33'], false, '---', true), 'test2-2---test1-1');

        Tree::removeSection($aSections['t11']);
    }

    /**
     *@covers \skewer\base\section\Tree::getAllSubsection
     */
    public function testGetAllSubsection()
    {
        $aSections = $this->createTestSections();

        $aIdeal = [
            $aSections['t21'] => $aSections['t21'],
            $aSections['t22'] => $aSections['t22'],
            $aSections['t31'] => $aSections['t31'],
            $aSections['t32'] => $aSections['t32'],
            $aSections['t33'] => $aSections['t33'],
        ];

        $this->assertEquals(Tree::getAllSubsection($aSections['t11']), $aIdeal);
        $falseId = (new Query())->select('MAX(`id`) as max')->from(TreeSection::tableName())->one();
        $this->assertEquals(Tree::getAllSubsection($falseId['max'] + 1000), []);

        Tree::removeSection($aSections['t11']);
    }

    /**
     *@covers \skewer\base\section\Tree::getSectionParents
     */
    public function testGetSectionParents()
    {
        $aSections = $this->createTestSections();

        $aParents = Tree::getSectionParents($aSections['t33']);

        foreach ($aParents as &$parent) {
            $parent = (string) $parent;
        }

        $aIdeal = [
            (string) $aSections['t22'],
            (string) $aSections['t11'],
            (string) \Yii::$app->sections->root(),
        ];

        $this->assertEquals($aParents, $aIdeal);
        $falseId = (new Query())->select('MAX(`id`) as max')->from(TreeSection::tableName())->one();
        $this->assertEquals(Tree::getSectionParent($falseId['max'] + 1000), 0);

        Tree::removeSection($aSections['t11']);
    }

    /**
     *@covers \skewer\base\section\Tree::getSectionsTitle
     */
    public function testGetSectionsTitle()
    {
        $aSections = $this->createTestSections();

        $this->assertEquals(Tree::getSectionsTitle($aSections['t33']), 'test3-3');

        $aIdeal = [
            $aSections['t11'] => 'test1-1',
            $aSections['t21'] => '-test2-1',
            $aSections['t31'] => '--test3-1',
            $aSections['t32'] => '--test3-2',
            $aSections['t22'] => '-test2-2',
            $aSections['t33'] => '--test3-3',
        ];

        $this->assertEquals(Tree::getSectionsTitle($aSections['t11'], true), $aIdeal);

        Tree::removeSection($aSections['t11']);
    }

    /**
     *@covers \skewer\base\section\Tree::getSubSections
     */
    public function testGetSubSections()
    {
        $aSections = $this->createTestSections();

        $oIdeal = TreeSection::find()
            ->where(['in', 'id', [$aSections['t21'], $aSections['t22']]])
            ->all();

        $this->assertEquals(Tree::getSubSections($aSections['t11']), $oIdeal);

        $aIdeal = TreeSection::find()
            ->where(['in', 'id', [$aSections['t21'], $aSections['t22']]])
            ->asArray()
            ->all();

        $aSubSections = Tree::getSubSections($aSections['t11'], true);

        $aIdeal = ArrayHelper::index($aIdeal, 'id');

        $this->assertEquals($aSubSections, $aIdeal);

        Tree::removeSection($aSections['t11']);
    }

    /**
     *@covers \skewer\base\section\Tree::getUserSectionTree
     */
    public function testGetUserSectionTree()
    {
        $aSections = $this->createTestSections();

        $aUserSection = Tree::getUserSectionTree($aSections['t11']);
        $this->assertEquals(count($aUserSection), 2);

        //хотя бы по количеству проверяем
        $this->assertTrue(count(Tree::getUserSectionTree(\Yii::$app->sections->root())) >= 4);
        Tree::removeSection($aSections['t11']);
    }
}
