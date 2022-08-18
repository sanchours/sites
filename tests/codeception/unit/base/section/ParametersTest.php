<?php

namespace unit\base\section;

use skewer\base\orm\Query;
use skewer\base\section\models\ParamsAr;
use skewer\base\section\models\ParamsAr as Params;
use skewer\base\section\Parameters;
use skewer\base\section\params\Type;
use skewer\base\section\Tree;
use unit\data\BackupHelper;

/**
 * Тест на параметры
 * Class ParametersTest.
 *
 * @group parameters_test
 */
class ParametersTest extends \Codeception\Test\Unit
{
    /** @var BackupHelper */
    protected $oBackUpHelper;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        require_once ROOTPATH . 'tests/codeception/unit/data/BackupHelper.php';

        $this->oBackUpHelper = new BackupHelper([
            'parameters' => ParamsAr::className(),
        ]);
    }

    protected function setUp()
    {
        $this->oBackUpHelper->backUpTables();

        ParamsAr::deleteAll();

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->oBackUpHelper->restoreTables();
        parent::tearDown();
    }

    /**
     * Создание - сохранение.
     *
     * @covers \skewer\base\section\Parameters::createParam
     */
    public function testSave()
    {
        $oParamNew = Parameters::createParam([
            'parent' => 4, 'name' => 'name', 'group' => 'test',
            'title' => '12', 'value' => 2, 'show_val' => 'text',
            'access_level' => 3, 'skewer' => '123',
        ]);

        $this->assertEquals($oParamNew->parent, 4);
        $this->assertEquals($oParamNew->name, 'name');
        $this->assertEquals($oParamNew->group, 'test');
        $this->assertEquals($oParamNew->title, '12');
        $this->assertEquals($oParamNew->value, 2);
        $this->assertEquals($oParamNew->show_val, 'text');
        $this->assertEquals($oParamNew->access_level, 3);

        $this->assertObjectNotHasAttribute('skewer', $oParamNew);

        $oParamNew->save();

        $aRow = Query::SelectFrom('parameters')->getOne();

        $this->assertEquals($oParamNew->parent, $aRow['parent']);
        $this->assertEquals($oParamNew->name, $aRow['name']);
        $this->assertEquals($oParamNew->group, $aRow['group']);
        $this->assertEquals($oParamNew->title, $aRow['title']);
        $this->assertEquals($oParamNew->value, $aRow['value']);
        $this->assertEquals($oParamNew->show_val, $aRow['show_val']);
        $this->assertEquals($oParamNew->access_level, $aRow['access_level']);

        /** Без группы */
        $oParamNew = Parameters::createParam([
            'parent' => 4, 'name' => 'name',
            'title' => '12', 'value' => 23, 'show_val' => 'text1111111111',
            'access_level' => 10, 'skewer' => '123',
        ]);
        $this->assertFalse($oParamNew->save());

        /** Повтор раздела-группы-имени */
        $oParamNew = Parameters::createParam([
            'parent' => 4, 'name' => 'name', 'group' => 'test',
            'title' => '12', 'value' => 23, 'show_val' => 'text1111111111',
            'access_level' => 10, 'skewer' => '123',
        ]);
        $this->assertFalse($oParamNew->save());

        /** Повтор раздела-группы-имени */
        $oParamNew = Parameters::createParam([
            'parent' => 4, 'name' => 'name', 'group' => 'test222',
            'title' => '12', 'value' => 23, 'show_val' => 'text1111111111',
            'access_level' => 10, 'skewer' => '123',
        ]);
        $this->assertTrue($oParamNew->save());

        /* Все правила валидации проверять не буду, доверимся Yii */
    }

    /**
     * Проверка на циклы шаблонов.
     *
     * @covers \skewer\base\section\params\TemplateValidator::validateValue
     * @covers \skewer\base\section\params\TemplateValidator::validateAttributes
     */
    public function testErrorTpl1()
    {
        $this->expectException(\yii\base\InvalidConfigException::class);

        Parameters::setParams(5, Parameters::settings, Parameters::template, 5);
    }

    /**
     * Проверка на циклы шаблонов.
     *
     * @covers \skewer\base\section\params\TemplateValidator::validateValue
     * @covers \skewer\base\section\params\TemplateValidator::validateAttributes
     */
    public function testErrorTpl2()
    {
        $this->expectException(\yii\base\InvalidConfigException::class);

        $this->assertTrue(Parameters::setParams(1, Parameters::settings, Parameters::template, 5) > 0);
        $this->assertTrue(Parameters::setParams(5, Parameters::settings, Parameters::template, 6) > 0);
        $this->assertTrue(Parameters::setParams(6, Parameters::settings, Parameters::template, 7) > 0);

        /* Тут должна быть ошибка! */
        Parameters::setParams(7, Parameters::settings, Parameters::template, 1);
    }

    /**
     * Проверка на циклы шаблонов.
     *
     * @covers \skewer\base\section\params\TemplateValidator::validateValue
     * @covers \skewer\base\section\params\TemplateValidator::validateAttributes
     */
    public function testErrorTpl3()
    {
        $this->expectException(\yii\base\InvalidConfigException::class);

        Parameters::setParams(5, Parameters::settings, Parameters::template, -5);
    }

    /**
     * Проверка на циклы шаблонов.
     *
     * @covers \skewer\base\section\params\TemplateValidator::validateValue
     * @covers \skewer\base\section\params\TemplateValidator::validateAttributes
     */
    public function testErrorTpl4()
    {
        $this->expectException(\yii\base\InvalidConfigException::class);

        Parameters::setParams(5, Parameters::settings, Parameters::template, 'template');
    }

    /**
     * @covers \skewer\base\section\Parameters::getById
     */
    public function testGetById()
    {
        $oParamNew = Parameters::createParam(['parent' => 1, 'name' => 2, 'group' => 4]);
        $oParamNew->save();

        $id = $oParamNew->id;

        $oParam = Parameters::getById($id);

        $this->assertTrue($oParam instanceof Params);
        $this->assertEquals($oParam->id, $oParamNew->id);
        $this->assertEquals($oParam->parent, $oParamNew->parent);
        $this->assertEquals($oParam->name, $oParamNew->name);
        $this->assertEquals($oParam->group, $oParamNew->group);

        $oParamNew->delete();

        $oParam = Parameters::getById($id);
        $this->assertNull($oParam);

        $oParam = Parameters::getById('123b134k');
        $this->assertNull($oParam);
    }

    /**
     * @covers \skewer\base\section\Parameters::getParamByName
     * @covers \skewer\base\section\Parameters::getByName
     */
    public function testGetByName()
    {
        Parameters::setParams(5, '4', '2', '1');
        Parameters::setParams(1, Parameters::settings, Parameters::template, 5);

        $oParamNew = Parameters::createParam(['parent' => 1, 'name' => 2, 'group' => 4, 'value' => 2]);
        $oParamNew->save();

        $oParam = Parameters::getByName(1, 4, 2, false);

        $this->assertTrue($oParam instanceof Params);
        $this->assertEquals($oParam->id, $oParamNew->id);
        $this->assertEquals($oParam->parent, $oParamNew->parent);
        $this->assertEquals($oParam->name, $oParamNew->name);
        $this->assertEquals($oParam->group, $oParamNew->group);
        $this->assertEquals($oParam->value, '2');

        $oParam = Parameters::getByName(1, 4, 2, true);

        $this->assertTrue($oParam instanceof Params);
        $this->assertEquals($oParam->id, $oParamNew->id);
        $this->assertEquals($oParam->parent, $oParamNew->parent);
        $this->assertEquals($oParam->name, $oParamNew->name);
        $this->assertEquals($oParam->group, $oParamNew->group);
        $this->assertEquals($oParam->value, '2');

        $oParamNew->delete();

        $oParam = Parameters::getByName(1, 4, 2);
        $this->assertFalse($oParam);

        $oParam = Parameters::getByName(1, 4, 2, true);

        $this->assertTrue($oParam instanceof Params);
        $this->assertEquals($oParam->parent, 5);
        $this->assertEquals($oParam->name, '2');
        $this->assertEquals($oParam->group, '4');
        $this->assertEquals($oParam->value, '1');

        /* Проверка параметров типа Type::paramInheritFromSection, Type::paramInheritFromSection*/

        Parameters::setParams(1, '2', 'inheritedTest', 2, 'show_val_5', null, Type::paramInheritFromSection);
        Parameters::setParams(2, '2', 'inheritedTest', 'vvv', 'www', null, Type::paramSystem);
        Parameters::setParams(2, '2', 'inheritedTest1', 3, '', null, Type::paramInheritFromSection);

        // вернет параметр из 2го раздела
        $oInheritedFromSectionParam = Parameters::getByName(1, '2', 'inheritedTest', true, false, true);

        $this->assertTrue($oInheritedFromSectionParam instanceof Params);
        $this->assertEquals($oInheritedFromSectionParam->parent, 2);
        $this->assertEquals($oInheritedFromSectionParam->group, '2');
        $this->assertEquals($oInheritedFromSectionParam->name, 'inheritedTest');
        $this->assertEquals($oInheritedFromSectionParam->value, 'vvv');
        $this->assertEquals($oInheritedFromSectionParam->show_val, 'www');

        // ссылается на параметр, которого нет
        $oInhrParam = Parameters::getByName(2, '2', 'inheritedTest1', true, false, true);
        $this->assertFalse($oInhrParam);

        /** Вложенность разделов s1 -> s2 -> s3 */
        $s1 = Tree::addSection(0, 's1');
        $s2 = Tree::addSection($s1->id, 's2');
        $s3 = Tree::addSection($s2->id, 's3');

        Parameters::setParams($s3->id, 'test_group', 'r1', 'r13_val', 'r13_show_val', null, Type::paramInherit);
        Parameters::setParams($s3->id, 'test_group', 'r2', 'r23_val', 'r23_show_val', null, Type::paramInherit);
        Parameters::setParams($s3->id, 'test_group', 'r3', 'r33_val', 'r33_show_val', null, Type::paramInherit);

        Parameters::setParams($s2->id, 'test_group', 'r1', 'r12_val', 'r12_show_val', null, Type::paramInherit);
        Parameters::setParams($s2->id, 'test_group', 'r3', 'r32_val', 'r32_show_val', null, Type::paramInherit);

        Parameters::setParams($s1->id, 'test_group', 'r1', 'r11_val', 'r11_show_val', null, Type::paramSystem);
        Parameters::setParams($s1->id, 'test_group', 'r2', 'r21_val', 'r21_show_val', null, Type::paramSystem);
        Parameters::setParams($s1->id, 'test_group', 'r3', 'r31_val', 'r31_show_val', null, Type::paramInherit);

        $r1 = Parameters::getByName($s3->id, 'test_group', 'r1', true, false, true); // возьмёт из 1, т.е. унаследован в 2 и 3
        $r2 = Parameters::getByName($s3->id, 'test_group', 'r2', true, false, true); // возьмет из 1, как из ближайщего родителя
        $r3 = Parameters::getByName($s3->id, 'test_group', 'r3', true, false, true); // возьмет из 3, т.к. не был перекрыт

        $this->assertTrue($r1 instanceof Params);
        $this->assertSame($r1->parent, $s1->id);
        $this->assertSame($r1->value, 'r11_val');
        $this->assertSame($r1->show_val, 'r11_show_val');

        $this->assertTrue($r2 instanceof Params);
        $this->assertSame($r2->parent, $s1->id);
        $this->assertSame($r2->value, 'r21_val');
        $this->assertSame($r2->show_val, 'r21_show_val');

        $this->assertTrue($r3 instanceof Params);
        $this->assertSame($r3->parent, $s3->id);
        $this->assertSame($r3->value, 'r33_val');
        $this->assertSame($r3->show_val, 'r33_show_val');

        Tree::removeSection($s1->id);
        Tree::removeSection($s2->id);
        Tree::removeSection($s3->id);
    }

    /**
     * @covers \skewer\base\section\Parameters::getParamByName
     * @covers \skewer\base\section\Parameters::getValByName
     * @covers \skewer\base\section\Parameters::getShowValByName
     */
    public function testGetVal()
    {
        Parameters::setParams(5, '5', '2', '1', '111');
        Parameters::setParams(4, Parameters::settings, Parameters::template, 5);

        /** Вложенность разделов s1 -> s2 -> s3 */
        $s1 = Tree::addSection(0, 's1');
        $s2 = Tree::addSection($s1->id, 's2');
        $s3 = Tree::addSection($s2->id, 's3');

        Parameters::setParams($s3->id, 'test_group', 'r1', 'r13_val', 'r13_show_val', null, Type::paramInherit);
        Parameters::setParams($s3->id, 'test_group', 'r2', 'r23_val', 'r23_show_val', null, Type::paramInherit);
        Parameters::setParams($s3->id, 'test_group', 'r3', 'r33_val', 'r33_show_val', null, Type::paramInherit);

        Parameters::setParams($s2->id, 'test_group', 'r1', 'r12_val', 'r12_show_val', null, Type::paramInherit);
        Parameters::setParams($s2->id, 'test_group', 'r3', 'r32_val', 'r32_show_val', null, Type::paramInherit);

        Parameters::setParams($s1->id, 'test_group', 'r1', 'r11_val', 'r11_show_val', null, Type::paramSystem);
        Parameters::setParams($s1->id, 'test_group', 'r2', 'r21_val', 'r21_show_val', null, Type::paramSystem);
        Parameters::setParams($s1->id, 'test_group', 'r3', 'r31_val', 'r31_show_val', null, Type::paramInherit);

        $oParamNew = Parameters::createParam([
            'parent' => 4, 'name' => 2, 'group' => 5,
            'value' => 'test', 'show_val' => 222,
        ]);
        $oParamNew->save();

        $this->assertEquals(Parameters::getValByName(4, 5, 2), 'test');
        $this->assertEquals(Parameters::getShowValByName(4, 5, 2), '222');

        $this->assertEquals(Parameters::getValByName(4, 5, 2, true), 'test');
        $this->assertEquals(Parameters::getShowValByName(4, 5, 2, true), '222');

        $oParamNew->value = 111111;
        $oParamNew->show_val = 'skewer';
        $oParamNew->save();

        $this->assertEquals(Parameters::getValByName(4, 5, 2), '111111');
        $this->assertEquals(Parameters::getShowValByName(4, 5, 2), 'skewer');

        $oParamNew->delete();

        $this->assertFalse(Parameters::getValByName(4, 5, 2));
        $this->assertFalse(Parameters::getShowValByName(4, 5, 2));

        $this->assertEquals(Parameters::getValByName(4, 5, 2, true), '1');
        $this->assertEquals(Parameters::getShowValByName(4, 5, 2, true), '111');

        $this->assertEquals(Parameters::getValByName($s3->id, 'test_group', 'r1', true, false, true), 'r11_val');
        $this->assertEquals(Parameters::getValByName($s3->id, 'test_group', 'r2', true, false, true), 'r21_val');
        $this->assertEquals(Parameters::getValByName($s3->id, 'test_group', 'r3', true, false, true), 'r33_val');

        Tree::removeSection($s1->id);
        Tree::removeSection($s2->id);
        Tree::removeSection($s3->id);
    }

    /**
     * @covers \skewer\base\section\Parameters::getListByModule
     * @covers \skewer\base\section\Parameters::getChildrenList
     */
    public function testGetListByModule()
    {
        Parameters::setParams(5, 'g1', Parameters::object, 'module1');
        Parameters::setParams(5, 'g2', Parameters::object, 'module2');
        Parameters::setParams(5, 'g3', Parameters::object, 'module3');

        Parameters::setParams(6, 'g1', Parameters::object, 'module4');
        Parameters::setParams(6, Parameters::settings, Parameters::template, 5);

        Parameters::setParams(7, 'g1', Parameters::object, 'module14');
        Parameters::setParams(7, Parameters::settings, Parameters::template, 5);

        Parameters::setParams(8, 'g3', Parameters::object, 'module1');
        Parameters::setParams(8, Parameters::settings, Parameters::template, 5);

        Parameters::setParams(9, Parameters::settings, Parameters::template, 6);

        $aSections = Parameters::getListByModule('module1', 'g1');
        sort($aSections);

        $this->assertEquals(count($aSections), 3);
        $this->assertEquals($aSections, [5, 8, 9]);

        $aSections = Parameters::getListByModule('module2', 'g2');
        sort($aSections);

        $this->assertEquals(count($aSections), 5);
        $this->assertEquals($aSections, [5, 6, 7, 8, 9]);

        $aSections = Parameters::getListByModule('module3', 'g3');
        sort($aSections);

        $this->assertEquals(count($aSections), 4);
        $this->assertEquals($aSections, [5, 6, 7, 9]);
    }

    /**
     * @covers \skewer\base\section\Parameters::getChildrenList
     * @covers \skewer\base\section\Parameters::getTpl
     * @covers \skewer\base\section\Parameters::getParentTemplates
     */
    public function testTemplates()
    {
        Parameters::setParams(6, Parameters::settings, Parameters::template, 5);
        Parameters::setParams(10, Parameters::settings, Parameters::template, 6);
        Parameters::setParams(18, Parameters::settings, Parameters::template, 10);
        Parameters::setParams(36, Parameters::settings, Parameters::template, 18);
        Parameters::setParams(45, Parameters::settings, Parameters::template, 36);
        Parameters::setParams(89, Parameters::settings, Parameters::template, 36);
        Parameters::setParams(62, Parameters::settings, Parameters::template, 89);
        Parameters::setParams(600, Parameters::settings, Parameters::template, 45);
        Parameters::setParams(11, Parameters::settings, Parameters::template, 600);

        $this->assertFalse(Parameters::getTpl(5));
        $this->assertSame([], Parameters::getParentTemplates(5));

        $this->assertEquals(Parameters::getTpl(18), 10);
        $this->assertEquals(Parameters::getTpl(11), 600);
        $this->assertEquals(Parameters::getTpl(6), 5);

        $aTemplates = Parameters::getParentTemplates(18);
        $this->assertEquals(count($aTemplates), 3);
        $this->assertContains(5, $aTemplates);
        $this->assertContains(6, $aTemplates);
        $this->assertContains(10, $aTemplates);

        $aTemplates = Parameters::getParentTemplates(600);
        $this->assertEquals(count($aTemplates), 6);
        $this->assertContains(5, $aTemplates);
        $this->assertContains(6, $aTemplates);
        $this->assertContains(10, $aTemplates);
        $this->assertContains(18, $aTemplates);
        $this->assertContains(36, $aTemplates);
        $this->assertContains(45, $aTemplates);

        $this->assertFalse(Parameters::getTpl(1111));
        $this->assertSame([], Parameters::getParentTemplates(1111));

        $aChilds = Parameters::getChildrenList(11);
        $this->assertFalse($aChilds);

        $aChilds = Parameters::getChildrenList(45);
        $this->assertEquals(count($aChilds), 2);
        $this->assertContains('11', $aChilds);
        $this->assertContains('600', $aChilds);

        $aChilds = Parameters::getChildrenList(89);
        $this->assertEquals(count($aChilds), 1);
        $this->assertContains('62', $aChilds);

        $aChilds = Parameters::getChildrenList(10);
        $this->assertEquals(count($aChilds), 7);
        $this->assertContains('62', $aChilds);
        $this->assertContains('45', $aChilds);
        $this->assertContains('11', $aChilds);

        $aChilds = Parameters::getChildrenList(5);
        $this->assertEquals(count($aChilds), 9);
        $this->assertContains('62', $aChilds);
        $this->assertContains('45', $aChilds);
        $this->assertContains('11', $aChilds);
    }

    /**
     * @covers \skewer\base\section\Parameters::updateByName
     */
    public function testUpdateByName()
    {
        Parameters::setParams(6, 'g1', 'n1', 5);
        Parameters::setParams(10, 'g1', 'n1', 6);
        Parameters::setParams(18, 'g1', 'n1', 10);
        Parameters::setParams(18, 'g2', 'n1', 10);

        $this->assertEquals(Parameters::updateByName('g1', 'n999', 8), 0);
        $this->assertEquals(Parameters::updateByName('', 'n999', 8), 0);
        $this->assertEquals(Parameters::updateByName('', '', 8), 0);
        $this->assertEquals(Parameters::updateByName('g1', 'n1', 8), 3);

        $aParams = Parameters::getList()->group('g1')->name('n1')->asArray()->get();
        foreach ($aParams as $param) {
            $this->assertEquals($param['value'], '8');
        }

        $this->assertEquals(Parameters::getValByName(18, 'g2', 'n1'), 10);
    }

    /**
     * @covers \skewer\base\section\Parameters::copyToSection
     */
    public function testCopy()
    {
        $oParam = Parameters::createParam([
            'parent' => 6, 'group' => 'g1', 'name' => 'n1', 'value' => 5,
        ]);
        $oParam->save();

        $this->assertFalse(Parameters::getByName(8, 'g1', 'n1'));
        $this->assertFalse(Parameters::getValByName(8, 'g1', 'n1'));

        $oParam = Parameters::copyToSection($oParam, 8);

        $this->assertTrue($oParam instanceof Params);
        $id = $oParam->id;

        $oParams = Parameters::getByName(8, 'g1', 'n1');
        $this->assertTrue($oParams instanceof Params);
        $this->assertEquals($oParam->id, $id);
        $this->assertEquals(Parameters::getValByName(8, 'g1', 'n1'), 5);

        $this->assertFalse(Parameters::copyToSection($oParam, 8));

        $oParam = Parameters::copyToSection($oParam, 11, 'new_val');

        $this->assertTrue($oParam instanceof Params);
        $id = $oParam->id;

        $oParams = Parameters::getByName(11, 'g1', 'n1');
        $this->assertTrue($oParams instanceof Params);
        $this->assertEquals($oParam->id, $id);
        $this->assertEquals(Parameters::getValByName(11, 'g1', 'n1'), 'new_val');
    }

    /**
     * @covers \skewer\base\section\Parameters::setParams
     */
    public function testSetParam()
    {
        $this->assertFalse(Parameters::getByName(3, 'g3', 'n5'));

        $this->assertTrue(Parameters::setParams(3, 'g3', 'n5') > 0);

        $oParam = Parameters::getByName(3, 'g3', 'n5');
        $this->assertTrue($oParam instanceof Params);
        $this->assertEquals($oParam->name, 'n5');
        $this->assertEquals($oParam->group, 'g3');
        $this->assertEquals($oParam->parent, 3);

        $this->assertTrue(Parameters::setParams(3, 'g3', 'n5', '123', 'rrr', 'qqq', 9) > 0);

        $oParam = Parameters::getByName(3, 'g3', 'n5');
        $this->assertTrue($oParam instanceof Params);
        $this->assertEquals($oParam->name, 'n5');
        $this->assertEquals($oParam->group, 'g3');
        $this->assertEquals($oParam->parent, 3);
        $this->assertEquals($oParam->value, '123');
        $this->assertEquals($oParam->show_val, 'rrr');
        $this->assertEquals($oParam->title, 'qqq');
        $this->assertEquals($oParam->access_level, 9);

        $this->assertTrue(Parameters::setParams(3, 'g3', 'n5', '1233333') > 0);

        $oParam = Parameters::getByName(3, 'g3', 'n5');
        $this->assertTrue($oParam instanceof Params);
        $this->assertEquals($oParam->name, 'n5');
        $this->assertEquals($oParam->group, 'g3');
        $this->assertEquals($oParam->parent, 3);
        $this->assertEquals($oParam->value, '1233333');
        $this->assertEquals($oParam->show_val, 'rrr');
        $this->assertEquals($oParam->title, 'qqq');
        $this->assertEquals($oParam->access_level, 9);

        $this->assertTrue(Parameters::setParams(33, 'g8', 'n45', 'eeeee') > 0);

        $oParam = Parameters::getByName(33, 'g8', 'n45');
        $this->assertTrue($oParam instanceof Params);
        $this->assertEquals($oParam->name, 'n45');
        $this->assertEquals($oParam->group, 'g8');
        $this->assertEquals($oParam->parent, 33);
        $this->assertEquals($oParam->value, 'eeeee');
    }

    /**
     * @covers \skewer\base\section\Parameters::removeByName
     * @covers \skewer\base\section\Parameters::removeById
     */
    public function testRemove()
    {
        Parameters::setParams(3, 'g3', 'n5', 'test');
        Parameters::setParams(3, 'g3', 'n45', 'test');

        $this->assertEquals(Parameters::getValByName(3, 'g3', 'n5'), 'test');

        $this->assertEquals(Parameters::removeByName('n5', 'g3', 3), 1);

        $this->assertFalse(Parameters::getValByName(3, 'g3', 'n5'));
        $this->assertFalse(Parameters::getByName(3, 'g3', 'n5'));

        $this->assertEquals(Parameters::removeByName('n5', 'g3', 3), 0);

        $id1 = Parameters::setParams(3, 'g3', 'n5', 'test');
        $id2 = Parameters::setParams(3, 'g3', 'n45', 'test');

        $this->assertEquals(Parameters::removeById([$id1, $id2]), 2);

        $this->assertNull(Parameters::getById($id1));
        $this->assertNull(Parameters::getById($id2));

        $id1 = Parameters::setParams(3, 'g3', 'n5', 'test');
        Parameters::setParams(3, 'g3', 'n45', 'test');

        $this->assertEquals(Parameters::removeById($id1), 1);

        $this->assertNull(Parameters::getById($id1));
        $this->assertEquals(Parameters::getValByName(3, 'g3', 'n45'), 'test');
    }

    /**
     * @covers \skewer\base\section\Parameters::getList
     * @covers \skewer\base\section\models\TreeSection::delete
     */
    public function testDeleteSection()
    {
        $oSection1 = Tree::addSection(3, '12212', 3);
        $iSection1 = $oSection1->id;
        $oSection2 = Tree::addSection($iSection1, '12212', 3);
        $iSection2 = $oSection2->id;
        $oSection3 = Tree::addSection($iSection2, '12212', 3);
        $iSection3 = $oSection3->id;
        $oSection4 = Tree::addSection(3, '12212', 3);
        $iSection4 = $oSection4->id;

        Parameters::setParams($iSection1, 'g', 'n1', 1);
        Parameters::setParams($iSection1, 'g', 'n2', 1);
        Parameters::setParams($iSection1, 'g', 'n3', 1);

        Parameters::setParams($iSection2, 'g', 'n1', 1);
        Parameters::setParams($iSection2, 'g', 'n2', 1);
        Parameters::setParams($iSection2, 'g', 'n3', 1);

        Parameters::setParams($iSection3, 'g', 'n1', 1);
        Parameters::setParams($iSection3, 'g', 'n2', 1);
        Parameters::setParams($iSection3, 'g', 'n3', 1);

        Parameters::setParams($iSection4, 'g', 'n1', 1);
        Parameters::setParams($iSection4, 'g', 'n2', 1);
        Parameters::setParams($iSection4, 'g', 'n3', 1);

        $iC1 = count(Parameters::getList($iSection1)->get());
        $iC2 = count(Parameters::getList($iSection2)->get());
        $iC3 = count(Parameters::getList($iSection3)->get());

        $oSection4->delete();

        $this->assertEquals($iC1, count(Parameters::getList($iSection1)->get()));
        $this->assertEquals($iC2, count(Parameters::getList($iSection2)->get()));
        $this->assertEquals($iC3, count(Parameters::getList($iSection3)->get()));
        $this->assertCount(0, Parameters::getList($iSection4)->get());

        $oSection1->delete();

        $this->assertCount(0, Parameters::getList($iSection1)->get());
        $this->assertCount(0, Parameters::getList($iSection2)->get());
        $this->assertCount(0, Parameters::getList($iSection3)->get());
        $this->assertCount(0, Parameters::getList($iSection4)->get());
    }
}
