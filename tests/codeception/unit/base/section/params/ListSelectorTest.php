<?php

namespace unit\base\section\params;

use skewer\base\section;
use skewer\base\section\models\ParamsAr;
use skewer\base\section\Parameters;
use skewer\base\section\params\ListSelector;
use skewer\base\section\params\Type;
use unit\data\BackupHelper;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 18.12.2015
 * Time: 13:59.
 */
class ListSelectorTest extends \Codeception\Test\Unit
{
    /** @var BackupHelper */
    protected $oBackupHelper;

    protected function setUp()
    {
        $this->oBackupHelper->backUpTables();

        ParamsAr::deleteAll();

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->oBackupHelper->restoreTables();
        parent::tearDown();
    }

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        require_once ROOTPATH . 'tests/codeception/unit/data/BackupHelper.php';

        $this->oBackupHelper = new BackupHelper([
            'parameters' => ParamsAr::className(),
        ]);
    }

    /**
     * Проверка запроса наследуемых по родителю (20 Type::paramInherit) записей.
     *
     * @covers \skewer\base\section\params\ListSelector::get
     */
    public function testInheritGet()
    {
        // добавляем 3 вложенных раздела
        $s1 = section\Tree::addSection(0, 'Section1');
        $this->assertNotEmpty($s1);

        $s2 = section\Tree::addSection($s1->id, 'Section2');
        $this->assertNotEmpty($s2);

        $s3 = section\Tree::addSection($s2->id, 'Section3');
        $this->assertNotEmpty($s3);

        /*
         * Параметры раздела нижнего уровня Section3
         * 1-5 - наследуемые
         * 6 - реальный
         */
        section\Parameters::setParams($s3->id, 'rec', 'a1', '', '', '', Type::paramInherit);
        section\Parameters::setParams($s3->id, 'rec', 'a2', '', '', '', Type::paramInherit);
        section\Parameters::setParams($s3->id, 'rec', 'a3', '', '', '', Type::paramInherit);
        section\Parameters::setParams($s3->id, 'rec', 'a4', '', '', '', Type::paramInherit);
        section\Parameters::setParams($s3->id, 'rec', 'a5', '', '', '', Type::paramSystem);
        section\Parameters::setParams($s3->id, 'rec', 'a6', 'r3', 'r3_show', '', Type::paramSystem);

        /*
         * Параметры раздела среднего уровня Section2
         * 1 - реальное значение
         * 2 - наследуемый
         */
        section\Parameters::setParams($s2->id, 'rec', 'a1', 'r2', 'r2_show', '', Type::paramSystem);
        section\Parameters::setParams($s2->id, 'rec', 'a2', '', '', '', Type::paramInherit);

        /*
         * Параметры раздела верхнего уровня Section1
         * 2 - реальное значение
         * 3 - реальное значение
         * 4 - наследуемый
         */
        section\Parameters::setParams($s1->id, 'rec', 'a1', 'r1', 'r1_show', '', Type::paramSystem);
        section\Parameters::setParams($s1->id, 'rec', 'a2', 'r12', 'r12_show', '', Type::paramSystem);
        section\Parameters::setParams($s1->id, 'rec', 'a3', 'r1', 'r1_show', '', Type::paramSystem);
        section\Parameters::setParams($s1->id, 'rec', 'a4', '', '', '', Type::paramInherit);

        // запрос данных
        $aParams = section\Parameters::getList($s3->id)->rec()->asArray()->get();

        // ркскладка по массивам
        $aData = [];
        $aShowData = [];
        foreach ($aParams as $aRow) {
            $aData[$aRow['name']] = $aRow['value'];
            $aShowData[$aRow['name']] = $aRow['show_val'];
        }

        /*
         * Проверка корректности сборки
         * 1-3 - унаследованы со значениями
         * 4-5 - не нашлось значения в родителях
         * 6 - реальный
         */
        $this->assertSame('r2', $aData['a1']);      // берется из 2, но есть и в 1
        $this->assertSame('r12', $aData['a2']);     // в 2 тоже наследуется, берется из 1
        $this->assertSame('r1', $aData['a3']);      // берется из 1
        $this->assertSame('', $aData['a4']);        // не нашлось, наследуется и верхнем уровне
        $this->assertSame('', $aData['a5']);        // вообще не встречается, кроме как в 3
        $this->assertSame('r3', $aData['a6']);      // задан еще в 3

        /* для расширенных рначений то же самое*/
        $this->assertSame('r2_show', $aShowData['a1']);      // берется из 2, но есть и в 1
        $this->assertSame('r12_show', $aShowData['a2']);     // в 2 тоже наследуется, берется из 1
        $this->assertSame('r1_show', $aShowData['a3']);      // берется из 1
        $this->assertSame('', $aShowData['a4']);        // не нашлось, наследуется и верхнем уровне
        $this->assertSame('', $aShowData['a5']);        // вообще не встречается, кроме как в 3
        $this->assertSame('r3_show', $aShowData['a6']);      // задан еще в 3
    }

    /**
     * @covers \skewer\base\section\params\ListSelector::group
     * @covers \skewer\base\section\params\ListSelector::name
     * @covers \skewer\base\section\params\ListSelector::parent
     * @covers \skewer\base\section\params\ListSelector::asArray
     * @covers \skewer\base\section\params\ListSelector::get
     */
    public function testGetListByNameGroup()
    {
        $aData = [
            [
                'parent' => 4, 'name' => 2, 'group' => 5, 'value' => '123',
            ],
            [
                'parent' => 5, 'name' => 2, 'group' => 5, 'value' => '456',
            ],
            [
                'parent' => 7, 'name' => 2, 'group' => 5, 'value' => '789',
            ],
            [
                'parent' => 7, 'name' => 6, 'group' => 5, 'value' => '156',
            ],
        ];

        foreach ($aData as $aRec) {
            Parameters::createParam($aRec)->save();
        }

        $aData = Parameters::getList()->group(5)->name(2)->asArray()->get();

        $this->assertInternalType('array', $aData);
        $this->assertEquals(count($aData), 3);

        $aData = ArrayHelper::map($aData, 'parent', 'value');

        $this->assertEquals($aData[4], '123');
        $this->assertEquals($aData[5], '456');
        $this->assertEquals($aData[7], '789');

        $aData = Parameters::getList()->parent([4, 7])->group(5)->name(2)->asArray()->get();

        $this->assertInternalType('array', $aData);
        $this->assertEquals(count($aData), 2);

        $aData = ArrayHelper::map($aData, 'parent', 'value');

        $this->assertEquals($aData[4], '123');
        $this->assertEquals($aData[7], '789');
    }

    /**
     * @covers \skewer\base\section\params\ListSelector::group
     * @covers \skewer\base\section\params\ListSelector::get
     */
    public function testGetListByGroup()
    {
        $aData = [
            [
                'parent' => 4, 'name' => 'param2', 'group' => 'group1', 'value' => '123',
            ],
            [
                'parent' => 4, 'name' => 'param1', 'group' => 'group1', 'value' => '456',
            ],
            [
                'parent' => 7, 'name' => 2, 'group' => 'group1', 'value' => '789',
            ],
        ];

        foreach ($aData as $aRec) {
            Parameters::createParam($aRec)->save();
        }

        $aParams = Parameters::getList(4)->group('group1')->get();
        $this->assertEquals(count($aParams), 2);
        foreach ($aParams as $oParam) {
            $this->assertInstanceOf(ParamsAr::className(), $oParam);
            $this->assertEquals($oParam->group, 'group1');
            $this->assertEquals($oParam->parent, 4);
        }
    }

    /**
     * @covers \skewer\base\section\params\ListSelector::get
     * @covers \skewer\base\section\params\ListSelector::name
     */
    public function testGetListByName()
    {
        $aData = [
            [
                'parent' => 4, 'name' => 'param1', 'group' => 'group1', 'value' => '123',
            ],
            [
                'parent' => 4, 'name' => 'param1', 'group' => 'group2', 'value' => '456',
            ],
            [
                'parent' => 4, 'name' => 'param2', 'group' => 'group2', 'value' => '4567',
            ],
            [
                'parent' => 7, 'name' => 'param1', 'group' => 'group1', 'value' => '789',
            ],
        ];

        foreach ($aData as $aRec) {
            Parameters::createParam($aRec)->save();
        }

        $aParams = Parameters::getList(4)->name('param1')->get();
        $this->assertEquals(count($aParams), 2);
        foreach ($aParams as $oParam) {
            $this->assertInstanceOf(ParamsAr::className(), $oParam);
            $this->assertEquals($oParam->name, 'param1');
            $this->assertEquals($oParam->parent, 4);
        }
    }

    /**
     * Тест на выборку по разделам
     *
     * @covers \skewer\base\section\params\ListSelector::get
     * @covers \skewer\base\section\params\ListSelector::rec
     * @covers \skewer\base\section\params\ListSelector::parent
     * @covers \skewer\base\section\params\ListSelector::groups
     */
    public function testGetByList()
    {
        /*
         * Шаблоны 5-6-7
         * Разделы 8-9
         */

        Parameters::setParams(5, 'g1', 'n1', 'v51', 'test', 'title', 0);
        Parameters::setParams(5, 'g1', 'n2', 'v52', 'test2', 'title2', -3);
        Parameters::setParams(5, 'g1', 'n3', 'v53', 'test3', 'title3', 3);
        Parameters::setParams(5, 'g2', 'n4', 'v54');
        Parameters::setParams(5, 'g2', 'n5', 'v55');
        Parameters::setParams(5, 'g3', 'n6', 'v56');

        Parameters::setParams(6, 'g1', 'n1', 'v61');
        Parameters::setParams(6, 'g1', 'n2', 'v62', 'test4', 'title4', 4);
        Parameters::setParams(6, 'g3', 'n7', 'v63');
        Parameters::setParams(6, Parameters::settings, Parameters::template, 5);

        Parameters::setParams(7, 'g1', 'n2', 'v71');
        Parameters::setParams(7, 'g3', 'n8', 'v72');
        Parameters::setParams(7, Parameters::settings, Parameters::template, 6);

        Parameters::setParams(8, Parameters::settings, Parameters::template, 7);

        $aParams = Parameters::getList(8)->rec()->groups()->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 4);
        foreach ($aParams as $k => $aGroup) {
            foreach ($aGroup as $aParam) {
                switch ($aParam->name) {
                    case 'n1':
                        $this->assertEquals($aParam->value, 'v61');
                        $this->assertEquals($aParam->parent, '6');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n2':
                        $this->assertEquals($aParam->value, 'v71');
                        $this->assertEquals($aParam->parent, '7');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n3':
                        $this->assertEquals($aParam->value, 'v53');
                        $this->assertEquals($aParam->parent, '5');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n4':
                        $this->assertEquals($aParam->value, 'v54');
                        $this->assertEquals($aParam->parent, '5');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n5':
                        $this->assertEquals($aParam->value, 'v55');
                        $this->assertEquals($aParam->parent, '5');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n6':
                        $this->assertEquals($aParam->value, 'v56');
                        $this->assertEquals($aParam->parent, '5');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n7':
                        $this->assertEquals($aParam->value, 'v63');
                        $this->assertEquals($aParam->parent, '6');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n8':
                        $this->assertEquals($aParam->value, 'v72');
                        $this->assertEquals($aParam->parent, '7');
                        $this->assertEquals($aParam->group, $k);
                        break;
                }
            }
        }

        /** parent */
        $aParams = Parameters::getList([7, 8])->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 4);

        /** оказывается yii пофиг $aParam->parent или $aParam['parent'] */
        foreach ($aParams as $aParam) {
            $this->assertTrue(in_array($aParam->parent, [7, 8]));
        }

        $aParams = Parameters::getList([7, 8])->rec()->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 4);

        /** group */
        $aParams = Parameters::getList()->group('g3')->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 3);

        foreach ($aParams as $aParam) {
            $this->assertEquals($aParam->group, 'g3');
        }

        $aParams = Parameters::getList(6)->group('g1')->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 2);

        foreach ($aParams as $aParam) {
            $this->assertEquals($aParam->group, 'g1');
            $this->assertEquals($aParam->parent, '6');
        }

        /** name */
        $aParams = Parameters::getList()->name('n2')->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 3);

        foreach ($aParams as $aParam) {
            $this->assertEquals($aParam->name, 'n2');
        }

        $aParams = Parameters::getList()->name('2323n2')->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 0);

        $aParams = Parameters::getList()->name(Parameters::template)->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 3);

        foreach ($aParams as $aParam) {
            $this->assertEquals($aParam->name, Parameters::template);
        }

        $aParams = Parameters::getList([6, 8])->name(Parameters::template)->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 2);

        foreach ($aParams as $aParam) {
            $this->assertEquals($aParam->name, Parameters::template);
            $this->assertTrue(in_array($aParam->parent, [6, 8]));
        }

        /** level */
        $aParams = Parameters::getList(5)->get();
        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 6);

        $aParams = Parameters::getList(5)->level(ListSelector::alEdit)->get();
        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 2);

        foreach ($aParams as $aParam) {
            $this->assertTrue($aParam->access_level != 0);
        }

        $aParams = Parameters::getList(5)->level(ListSelector::alPos)->get();
        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 1);

        foreach ($aParams as $aParam) {
            $this->assertTrue($aParam->access_level > 0);
        }

        $aParams = Parameters::getList()->level(ListSelector::alEdit)->get();
        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 3);

        foreach ($aParams as $aParam) {
            $this->assertTrue($aParam->access_level != 0);
        }

        $aParams = Parameters::getList()->level(ListSelector::alPos)->get();
        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 2);

        foreach ($aParams as $aParam) {
            $this->assertTrue($aParam->access_level > 0);
        }

        /** fields */
        $aParams = Parameters::getList()->get();
        $this->assertInternalType('array', $aParams);

        foreach ($aParams as $aParam) {
            $this->assertTrue(isset($aParam->access_level));
        }

        $aParams = Parameters::getList()->fields(['value'])->get();
        $this->assertInternalType('array', $aParams);

        foreach ($aParams as $aParam) {
            $this->assertTrue(isset($aParam->value));
            $this->assertFalse(isset($aParam->access_level));
        }

        /** asArray */
        $aParams = Parameters::getList()->asArray()->get();
        $this->assertInternalType('array', $aParams);

        foreach ($aParams as $aParam) {
            $this->assertInternalType('array', $aParam);
        }

        $aParams = Parameters::getList()->get();
        $this->assertInternalType('array', $aParams);

        foreach ($aParams as $aParam) {
            $this->assertInstanceOf(ParamsAr::className(), $aParam);
        }

        /** other */
        $aParams = Parameters::getList(8)->groups()->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 1);
        foreach ($aParams as $k => $aGroup) {
            foreach ($aGroup as $aParam) {
                $this->assertEquals($aParam->parent, '8');
            }
        }
        Parameters::setParams(8, 'g1', 'n3', 'v88');
        Parameters::setParams(8, 'g3', 'n7', 'v89');
        Parameters::setParams(8, 'g3', 'n9', 'v810');

        $aParams = Parameters::getList(8)->rec()->groups()->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 4);
        foreach ($aParams as $k => $aGroup) {
            foreach ($aGroup as $aParam) {
                switch ($aParam->name) {
                    case 'n1':
                        $this->assertEquals($aParam->value, 'v61');
                        $this->assertEquals($aParam->parent, '6');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n2':
                        $this->assertEquals($aParam->value, 'v71');
                        $this->assertEquals($aParam->parent, '7');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n3':
                        $this->assertEquals($aParam->value, 'v88');
                        $this->assertEquals($aParam->parent, '8');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n4':
                        $this->assertEquals($aParam->value, 'v54');
                        $this->assertEquals($aParam->parent, '5');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n5':
                        $this->assertEquals($aParam->value, 'v55');
                        $this->assertEquals($aParam->parent, '5');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n6':
                        $this->assertEquals($aParam->value, 'v56');
                        $this->assertEquals($aParam->parent, '5');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n7':
                        $this->assertEquals($aParam->value, 'v89');
                        $this->assertEquals($aParam->parent, '8');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n8':
                        $this->assertEquals($aParam->value, 'v72');
                        $this->assertEquals($aParam->parent, '7');
                        $this->assertEquals($aParam->group, $k);
                        break;
                    case 'n9':
                        $this->assertEquals($aParam->value, 'v810');
                        $this->assertEquals($aParam->parent, '8');
                        $this->assertEquals($aParam->group, $k);
                        break;
                }
            }
        }

        $aParams = Parameters::getList(8)->groups()->get();

        $this->assertInternalType('array', $aParams);
        $this->assertEquals(count($aParams), 3);
        foreach ($aParams as $k => $aGroup) {
            foreach ($aGroup as $aParam) {
                $this->assertEquals($aParam->parent, '8');
            }
        }
    }
}
