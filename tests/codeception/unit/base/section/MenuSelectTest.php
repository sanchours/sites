<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 26.06.2015
 * Time: 13:56.
 */

namespace unit\base\section;

use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\components\auth\CurrentUser;
use skewer\components\auth\Policy;
use yii\helpers\ArrayHelper;

class MenuSelectTest extends \Codeception\Test\Unit
{
    private $root_id;
    private $ids = [];

    protected function setUp()
    {
        $root = Tree::addSection(\Yii::$app->sections->main(), 'root');

        $this->root_id = $root->id;

        $ids = [];

        foreach (['1', '2', '3', '4', '5', '6'] as $level1) {
            if ($level1 == '5') {
                $l1 = Tree::addSection($root->id, 's' . $level1, 0, '', Visible::HIDDEN_FROM_MENU);
            } else {
                $l1 = Tree::addSection($root->id, 's' . $level1);
            }
            $ids[$l1->title] = $l1->id;
            foreach (['1', '2', '3', '4', '5', '6'] as $level2) {
                if ($level2 == '5') {
                    $l2 = Tree::addSection($l1->id, $l1->title . $level2, 0, '', Visible::HIDDEN_FROM_MENU);
                } else {
                    $l2 = Tree::addSection($l1->id, $l1->title . $level2);
                }
                $ids[$l2->title] = $l2->id;
                foreach (['1', '2', '3', '4', '5', '6'] as $level3) {
                    if ($level3 == '5') {
                        $l3 = Tree::addSection($l2->id, $l2->title . $level3, 0, '', Visible::HIDDEN_FROM_MENU);
                    } else {
                        $l3 = Tree::addSection($l2->id, $l2->title . $level3);
                    }
                    $ids[$l3->title] = $l3->id;
                }
            }
        }

        Policy::incPolicyVersion();
        Policy::updateCache(CurrentUser::getPolicyId());
        CurrentUser::reloadPolicy();
        Tree::dropCache();

        $this->ids = $ids;
    }

    protected function tearDown()
    {
        Tree::removeSection($this->root_id);
    }

    private function getFromTree($name, $aTree)
    {
        $l1 = mb_substr($name, 0, 2);
        $l2 = mb_substr($name, 0, 3);
        $l3 = mb_substr($name, 0, 4);

        switch (mb_strlen($name)) {
            case 2:
                $al1 = ArrayHelper::getColumn($aTree, 'title');
                if (($l1 = array_search($l1, $al1)) !== false) {
                    return $aTree[$l1];
                }

                    return [];
                break;

            case 3:
                $al1 = ArrayHelper::getColumn($aTree, 'title');
                if (($l1 = array_search($l1, $al1)) !== false) {
                    $al2 = ArrayHelper::getColumn($aTree[$l1]['items'], 'title');
                    if (($l2 = array_search($l2, $al2)) !== false) {
                        return $aTree[$l1]['items'][$l2];
                    }

                    return [];
                }

                    return [];

                break;

            case 4:

                $al1 = ArrayHelper::getColumn($aTree, 'title');
                if (($l1 = array_search($l1, $al1)) !== false) {
                    $al2 = ArrayHelper::getColumn($aTree[$l1]['items'], 'title');
                    if (($l2 = array_search($l2, $al2)) !== false) {
                        $al3 = ArrayHelper::getColumn($aTree[$l1]['items'][$l2]['items'], 'title');
                        if (($l3 = array_search($l3, $al3)) !== false) {
                            return $aTree[$l1]['items'][$l2]['items'][$l3];
                        }

                        return [];
                    }

                    return [];
                }

                    return [];
                break;

            default:
                $this->fail("Кривое имя[{$name}]");
        }

        return [];
    }

    /**
     * Проверка запроса на 1 урорвень.
     *
     * @covers \skewer\base\section\Tree::getUserSectionTree
     */
    public function testNormal()
    {
        $t = Tree::getUserSectionTree($this->root_id, 0, 1);

        $this->assertNotEmpty($this->getFromTree('s1', $t));
        $this->assertNotEmpty($this->getFromTree('s2', $t));
        $this->assertNotEmpty($this->getFromTree('s3', $t));
        $this->assertNotEmpty($this->getFromTree('s4', $t));
        $this->assertNotEmpty($this->getFromTree('s6', $t));

        //не должно быть скрытого раздела 1 го уровня
        $this->assertEmpty($this->getFromTree('s5', $t));
        //не должно быть 2го и 3го уровня
        $this->assertEmpty($this->getFromTree('s123', $t));
        $this->assertEmpty($this->getFromTree('s11', $t));
        $this->assertEmpty($this->getFromTree('s111', $t));
        $this->assertEmpty($this->getFromTree('s45', $t));
        $this->assertEmpty($this->getFromTree('s64', $t));
        $this->assertEmpty($this->getFromTree('s444', $t));
    }

    /**
     * Проверка запроса на 2 уровня.
     *
     * @covers \skewer\base\section\Tree::getUserSectionTree
     */
    public function test2Level()
    {
        $t = Tree::getUserSectionTree($this->root_id, 0, 2);

        //должны быть видимые разделы 1го уровня
        $this->assertNotEmpty($this->getFromTree('s1', $t));
        $this->assertNotEmpty($this->getFromTree('s2', $t));
        $this->assertNotEmpty($this->getFromTree('s3', $t));
        $this->assertNotEmpty($this->getFromTree('s4', $t));
        $this->assertNotEmpty($this->getFromTree('s6', $t));

        //должны быть видимые разделы 2го уровня с видимым родителем
        $this->assertNotEmpty($this->getFromTree('s11', $t));
        $this->assertNotEmpty($this->getFromTree('s23', $t));
        $this->assertNotEmpty($this->getFromTree('s33', $t));
        $this->assertNotEmpty($this->getFromTree('s31', $t));
        $this->assertNotEmpty($this->getFromTree('s42', $t));
        $this->assertNotEmpty($this->getFromTree('s64', $t));
        $this->assertNotEmpty($this->getFromTree('s66', $t));

        //не должно быть скрытых разделов 1го уровня
        $this->assertEmpty($this->getFromTree('s5', $t));
        //не должно быть скрытых разделов 2го уровня с видимым родителем
        $this->assertEmpty($this->getFromTree('s15', $t));
        $this->assertEmpty($this->getFromTree('s45', $t));
        //не должно быть скрытых разделов 2го уровня со скрытым родителем
        $this->assertEmpty($this->getFromTree('s55', $t));
        //не должно быть видимого раздела 2го уровня со скрытым родителем
        $this->assertEmpty($this->getFromTree('s54', $t));
        $this->assertEmpty($this->getFromTree('s52', $t));
        //не должно быть разделов 3го уровня
        $this->assertEmpty($this->getFromTree('s123', $t));
        $this->assertEmpty($this->getFromTree('s111', $t));
        $this->assertEmpty($this->getFromTree('s436', $t));
        $this->assertEmpty($this->getFromTree('s613', $t));
    }

    /**
     * Проверка запроса на 3 уровня.
     *
     * @covers \skewer\base\section\Tree::getUserSectionTree
     */
    public function test3Level()
    {
        $t = Tree::getUserSectionTree($this->root_id, 0, 4);

        //должны быть видимые разделы 1го уровня
        $this->assertNotEmpty($this->getFromTree('s1', $t));
        $this->assertNotEmpty($this->getFromTree('s2', $t));
        $this->assertNotEmpty($this->getFromTree('s3', $t));
        $this->assertNotEmpty($this->getFromTree('s4', $t));
        $this->assertNotEmpty($this->getFromTree('s6', $t));
        //должны быть видимые разделы 2го уровня с видимым родителем
        $this->assertNotEmpty($this->getFromTree('s11', $t));
        $this->assertNotEmpty($this->getFromTree('s23', $t));
        $this->assertNotEmpty($this->getFromTree('s33', $t));
        $this->assertNotEmpty($this->getFromTree('s31', $t));
        $this->assertNotEmpty($this->getFromTree('s42', $t));
        $this->assertNotEmpty($this->getFromTree('s64', $t));
        $this->assertNotEmpty($this->getFromTree('s66', $t));
        //должны быть видимые разделы 3го уровня с видимыми родителями
        $this->assertNotEmpty($this->getFromTree('s114', $t));
        $this->assertNotEmpty($this->getFromTree('s233', $t));
        $this->assertNotEmpty($this->getFromTree('s333', $t));
        $this->assertNotEmpty($this->getFromTree('s311', $t));
        $this->assertNotEmpty($this->getFromTree('s426', $t));
        $this->assertNotEmpty($this->getFromTree('s641', $t));
        $this->assertNotEmpty($this->getFromTree('s663', $t));

        //не должно быть скрытых разделов 1го уровня
        $this->assertEmpty($this->getFromTree('s5', $t));
        //не должно быть скрытых разделов 2го уровня с видимым родителем
        $this->assertEmpty($this->getFromTree('s15', $t));
        $this->assertEmpty($this->getFromTree('s45', $t));
        //не должно быть скрытых разделов 3го уровня с видимыми родителями
        $this->assertEmpty($this->getFromTree('s125', $t));
        $this->assertEmpty($this->getFromTree('s435', $t));
        $this->assertEmpty($this->getFromTree('s615', $t));
        //не должно быть скрытых разделов 2го уровня со скрытым родителем
        $this->assertEmpty($this->getFromTree('s55', $t));
        //не должно быть скрытых разделов 3го уровня со скрытыми родителями
        $this->assertEmpty($this->getFromTree('s555', $t));
        //не должно быть видимого раздела 2го уровня со скрытым родителем
        $this->assertEmpty($this->getFromTree('s54', $t));
        $this->assertEmpty($this->getFromTree('s52', $t));
        //не должно быть видимого раздела 3го уровня со скрытым родителем
        $this->assertEmpty($this->getFromTree('s541', $t));
        $this->assertEmpty($this->getFromTree('s523', $t));
        $this->assertEmpty($this->getFromTree('s253', $t));
        $this->assertEmpty($this->getFromTree('s156', $t));
        $this->assertEmpty($this->getFromTree('s553', $t));
    }

    /**
     * Проверка запроса на 1 урорвень с открытой веткой.
     *
     * @covers \skewer\base\section\Tree::getUserSectionTree
     */
    public function test1LevelWith()
    {
        $t = Tree::getUserSectionTree($this->root_id, $this->ids['s123'], 1);

        $this->assertNotEmpty($this->getFromTree('s1', $t));
        $this->assertNotEmpty($this->getFromTree('s2', $t));

        // целевая присутствует
        $this->assertNotEmpty($this->getFromTree('s123', $t));

        // другие ветки отсутствуют
        $this->assertEmpty($this->getFromTree('s21', $t));
        $this->assertEmpty($this->getFromTree('s31', $t));
        $this->assertEmpty($this->getFromTree('s41', $t));
        $this->assertEmpty($this->getFromTree('s51', $t));
        $this->assertEmpty($this->getFromTree('s61', $t));

        //не должно быть скрытого раздела 1 го уровня
        $this->assertEmpty($this->getFromTree('s5', $t));
        //не должно быть 2го и 3го уровня не целевых веток
        $this->assertEmpty($this->getFromTree('s21', $t));
        $this->assertEmpty($this->getFromTree('s211', $t));
        $this->assertEmpty($this->getFromTree('s45', $t));
        $this->assertEmpty($this->getFromTree('s64', $t));
        $this->assertEmpty($this->getFromTree('s444', $t));
    }
}
