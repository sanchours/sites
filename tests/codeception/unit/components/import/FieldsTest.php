<?php

namespace unit\components\import;

use skewer\base\orm\Query;
use skewer\base\queue;
use skewer\components\catalog;
use skewer\components\import\Api;
use skewer\components\import\ar\ImportTemplate;
use skewer\components\import\field\Prototype;
use skewer\components\import\field\Title;
use skewer\components\import\field\Unique;
use skewer\components\import\field\Value;
use skewer\components\import\Task;

class FieldsTest extends \Codeception\Test\Unit
{
    /**
     * Вызвать private метод класса в PHP.
     *
     * @param $object
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    private function callPrivateMethod($object, $method, $args = [])
    {
        $classReflection = new \ReflectionClass(get_class($object));
        $methodReflection = $classReflection->getMethod($method);
        $methodReflection->setAccessible(true);
        $result = $methodReflection->invokeArgs($object, $args);
        $methodReflection->setAccessible(false);

        return $result;
    }

    const card = 1;
    const EXT_CARD = 'test_card';

    /** @var Task */
    private $task = false;

    /**
     * @var catalog\GoodsRow
     */
    private $oRow = false;

    protected function setUp()
    {
        self::clearAllData();

        $iBaseCard = catalog\Generator::genBaseCard();
        $oExtCard = catalog\Generator::createExtCard($iBaseCard, self::EXT_CARD);
        $oExtCard->updCache();

        $this->oRow = catalog\GoodsRow::create(self::EXT_CARD);
        $this->oRow->save();

        parent::setUp();
    }

    protected function tearDown()
    {
        /* #65144_removeCard */
        if ($oCard = catalog\Card::get(catalog\Card::DEF_BASE_CARD)) {
            $oCard->delete();
        }

        catalog\model\GoodsTable::removeCard($oCard->id);

        if ($oCard = catalog\Card::get(self::EXT_CARD)) {
            $oCard->delete();
        }

        catalog\model\GoodsTable::removeCard($oCard->id);
    }

    /**
     * @param $sType
     * @param $sName
     * @param $importFields
     * @param array $aParams
     *
     * @return mixed
     * @covers \skewer\components\import\Field\Prototype::__construct
     * @covers \skewer\components\import\Field\Prototype::initParams
     */
    private function getField($sType, $sName, $importFields, $aParams = [])
    {
        $sClassName = 'skewer\\components\\import\\field\\' . $sType;

        $aData = [];
        foreach ($aParams as $k => $v) {
            $aData['fields'][$sName]['params'][$k] = $v;
        }

        if (class_exists($sClassName)) {
            $iTpl = ImportTemplate::getNewRow([
                'card' => self::EXT_CARD,
                'type' => Api::Type_File,
                'settings' => json_encode($aData),
            ])->save();

            $iTask = queue\Api::addTask([
                'class' => '\skewer\components\import\Task',
                'priority' => queue\Task::priorityHigh,
                'resource_use' => Task::weightLow,
                'title' => \Yii::t('import', 'task_title', 'test'),
                'parameters' => ['tpl' => (int) $iTpl],
            ]);

            $oTask = queue\Api::getTaskById($iTask);

            $oTask->init();

            $this->task = $oTask;

            /** @var Prototype $oField */
            $oField = new $sClassName(explode(',', $importFields), $sName, $oTask);

            $this->assertEquals($oField->getName(), $sName);

            /* Проверка параметров */
            foreach ($aParams as $k => $v) {
                $this->assertAttributeEquals($v, $k, $oField);
            }

            return $oField;
        }
    }

    /**
     * @covers \skewer\components\import\Field\Prototype::loadData
     * @covers \skewer\components\import\Field\Prototype::dropDown
     */
    public function testField()
    {
        /** @var Prototype $oField */
        $oField = $this->getField('Active', 'active_field', '1,6,3');

        $oField->loadData([
            '1' => 'test1',
            '2' => 'test2',
            '3' => 'test3',
            '4' => 'test4',
            '5' => 'test5',
            '6' => 'test6',
        ]);

        $this->assertAttributeEquals(['1' => 'test1', '6' => 'test6', '3' => 'test3'], 'values', $oField);

        $oField->dropDown();
        $this->assertAttributeEquals([], 'values', $oField);
    }

    /**
     * Находим товар
     *
     * @covers \skewer\components\import\Field\Unique::beforeSave
     */
    public function testUniqueField()
    {
        /** @var Unique $oField */
        $oField = $this->getField('Unique', 'article', '1', ['create' => false]);

        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        $this->assertFalse((bool) $row);

        $oField->init();
        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        $this->assertFalse((bool) $row);

        $art = '123456skewer';
        $oRow = catalog\GoodsRow::create(self::EXT_CARD);
        $oRow->setData(['article' => $art, 'title' => 'skewer']);
        $oRow->save();

        $oField->loadData(['1' => $art]);

        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        $this->assertFalse((bool) $row);

        $oField->beforeExecute();

        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        /* @var $row catalog\GoodsRow */
        $this->assertTrue($row instanceof catalog\GoodsRow);

        $this->assertEquals($oRow->getRowId(), $row->getRowId());

        $this->assertEquals($art, $row->getData()['article']);

        $this->assertEquals($oField->getValue(), $art);

        $oField->afterSave();

        $oField->shutdown();
    }

    /**
     * Не находим товар
     *
     * @covers \skewer\components\import\Field\Unique::beforeSave
     */
    public function testUniqueField2()
    {
        /** @var Unique $oField */
        $oField = $this->getField('Unique', 'article', '1', ['create' => false]);

        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        $this->assertFalse($row);

        $oField->init();
        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        $this->assertFalse($row);

        $art = '123456skewer';
        $oRow = catalog\GoodsRow::create(self::EXT_CARD);
        $oRow->setData(['article' => $art, 'title' => 'skewer']);
        $oRow->save();

        $oField->loadData(['1' => '123456skewer111']);

        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        $this->assertFalse($row);

        $oField->beforeSave();

        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        $this->assertFalse($row);

        $oField->getValue();

        $this->assertFalse($row);

        $oField->afterSave();

        $this->assertFalse($row);

        $oField->shutdown();

        $this->assertFalse($row);
    }

    /**
     * Не находим товар, но создаем
     *
     * @covers \skewer\components\import\Field\Unique::beforeSave
     * @covers \skewer\components\import\Field\Unique::getValue
     */
    public function testUniqueField3()
    {
        /** @var Unique $oField */
        $oField = $this->getField('Unique', 'article', '1', ['create' => true]);

        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        $this->assertFalse((bool) $row);

        $oField->init();
        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        $this->assertFalse((bool) $row);

        $art = 123456;
        $oRow = catalog\GoodsRow::create(self::EXT_CARD);
        $oRow->setData(['article' => $art, 'title' => 'skewer']);
        $oRow->save();

        $oField->loadData(['1' => 123459]);

        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        $this->assertFalse((bool) $row);

        $oField->beforeExecute();

        $row = $this->callPrivateMethod($oField, 'getGoodsRow', []);
        /* @var $row catalog\GoodsRow */
        $this->assertTrue($row instanceof catalog\GoodsRow);

        $this->assertTrue($oRow->getRowId() != $row->getRowId());

        $this->callPrivateMethod($oField, 'execute', []);

        $this->assertEquals('123459', $row->getData()['article']);

        $this->assertEquals($oField->getValue(), '123459');

        $oField->afterSave();

        $oField->shutdown();
    }

    /**
     * Value.
     *
     * @covers \skewer\components\import\Field\Value::getValue
     */
    public function testValueField()
    {
        /** @var Value $oField */
        $oField = $this->getField('Value', 'price', '4');

        $this->callPrivateMethod($oField, 'setGoodsRow', [$this->oRow]);

        $price = 100500;

        $this->assertNotEquals($this->oRow->getData()['price'], $price);

        $oField->init();

        $this->assertNotEquals($this->oRow->getData()['price'], $price);

        $oField->loadData(['4' => $price]);

        $this->assertNotEquals($this->oRow->getData()['price'], $price);

        $oField->beforeSave();

        $this->assertNotEquals($this->oRow->getData()['price'], $price);

        $this->callPrivateMethod($oField, 'execute', []);

        $this->assertEquals($this->oRow->getData()['price'], $price);

        $oField->afterSave();

        $this->assertEquals($this->oRow->getData()['price'], $price);

        $oField->shutdown();

        $this->assertEquals($this->oRow->getData()['price'], $price);
    }

    /**
     * Title.
     *
     * @covers \skewer\components\import\Field\Title::beforeSave
     * @covers \skewer\components\import\Field\Title::getValue
     */
    public function testTitleField()
    {
        /** @var Title $oField */
        $oField = $this->getField('Title', 'title', '4');

        $oConfig = $this->task->getConfig();

        $this->callPrivateMethod($oField, 'setGoodsRow', [$this->oRow]);

        $title = 'Товарчик';

        $this->assertNotEquals($oConfig->getParam('current_title'), $title);
        $this->assertNotEquals($this->oRow->getData()['title'], $title);

        $oField->init();

        $this->assertNotEquals($oConfig->getParam('current_title'), $title);
        $this->assertNotEquals($this->oRow->getData()['title'], $title);

        $oField->loadData(['4' => $title]);

        $this->assertNotEquals($oConfig->getParam('current_title'), $title);
        $this->assertNotEquals($this->oRow->getData()['title'], $title);

        $oField->beforeExecute();

        $this->assertEquals($oConfig->getParam('current_title'), $title);
        $this->assertNotEquals($this->oRow->getData()['title'], $title);

        $this->callPrivateMethod($oField, 'execute', []);

        $this->assertEquals($oConfig->getParam('current_title'), $title);
        $this->assertEquals($this->oRow->getData()['title'], $title);

        $oField->afterSave();

        $this->assertEquals($oConfig->getParam('current_title'), $title);
        $this->assertEquals($this->oRow->getData()['title'], $title);

        $oField->shutdown();

        $this->assertEquals($oConfig->getParam('current_title'), $title);
        $this->assertEquals($this->oRow->getData()['title'], $title);
    }

    /**
     * Empty Title.
     *
     * @covers \skewer\components\import\Field\Title::beforeSave
     * @covers \skewer\components\import\Field\Title::getValue
     */
    public function testTitleField2()
    {
        /** @var Title $oField */
        $oField = $this->getField('Title', 'title', '4');

        $oConfig = $this->task->getConfig();

        $this->callPrivateMethod($oField, 'setGoodsRow', [$this->oRow]);

        $title = 'Товарчик';

        $this->assertNotEquals($oConfig->getParam('current_title'), $title);
        $this->assertNotEquals($this->oRow->getData()['title'], $title);

        $oField->init();

        $this->assertNotEquals($oConfig->getParam('current_title'), $title);
        $this->assertNotEquals($this->oRow->getData()['title'], $title);

        $oField->loadData(['4' => '']);

        $this->assertNotEquals($oConfig->getParam('current_title'), $title);
        $this->assertNotEquals($this->oRow->getData()['title'], $title);

        $this->assertAttributeEquals(false, 'skipCurrentRow', $this->task);

        $oField->beforeExecute();

        $this->assertAttributeEquals(true, 'skipCurrentRow', $this->task);

        $this->assertNotEquals($oConfig->getParam('current_title'), $title);
        $this->assertNotEquals($this->oRow->getData()['title'], $title);

        $this->callPrivateMethod($oField, 'execute', []);

        $this->assertNotEquals($oConfig->getParam('current_title'), $title);
        $this->assertNotEquals($this->oRow->getData()['title'], $title);

        $oField->afterSave();

        $this->assertNotEquals($oConfig->getParam('current_title'), $title);
        $this->assertNotEquals($this->oRow->getData()['title'], $title);

        $oField->shutdown();

        $this->assertNotEquals($oConfig->getParam('current_title'), $title);
        $this->assertNotEquals($this->oRow->getData()['title'], $title);
    }

    protected function clearAllData()
    {
        Query::truncateTable('c_entity');
        Query::truncateTable('c_field');
        Query::truncateTable('c_validator');
        Query::truncateTable('c_field_attr');
        Query::truncateTable('c_field_group');
    }
}
