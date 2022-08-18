<?php

namespace unit\components\catalog\model;

use skewer\base\orm\Query;
use skewer\components\catalog\Attr;
use skewer\components\catalog\model\EntityTable;
use skewer\components\catalog\model\FieldGroupTable;
use skewer\components\catalog\model\FieldTable;

class EntityRowTest extends \Codeception\Test\Unit
{
    protected function clearAllData()
    {
        Query::SQL('TRUNCATE c_entity');
        Query::SQL('TRUNCATE c_field');
        Query::SQL('TRUNCATE c_validator');
        Query::SQL('TRUNCATE c_field_attr');
        Query::SQL('TRUNCATE c_field_group');
    }

    protected function createCard()
    {
        $oCard = EntityTable::getNewRow();
        $oCard->title = 'test';
        $oCard->save();

        return $oCard;
    }

    protected function createGroup($data)
    {
        $oGroup = FieldGroupTable::getNewRow();
        $oGroup->setData($data);
        $oGroup->save();

        return $oGroup;
    }

    protected function createField($data)
    {
        $oField = FieldTable::getNewRow();
        $oField->setData($data);
        $oField->save();

        return $oField;
    }

    protected function createRow()
    {
    }

    protected function setUp()
    {
        $this->clearAllData();
    }

    protected function setDown()
    {
    }

    /**
     * сохранение с получением идентификатора
     * уникальное имя сущности
     * построение модели - отдельный набор тестов в конце.
     */

    /**
     * @covers \skewer\components\catalog\model\EntityRow::checkUniqueName
     */
    public function testSaveCheckUniqueName()
    {
        // create name
        $oCard = EntityTable::getNewRow();
        $oCard->title = 'test';
        $oCard->save();

        $oResCard = EntityTable::find($oCard->id);

        $this->assertNotEmpty($oResCard->name);
        $this->assertSame($oResCard->name, $oCard->name);
        $this->assertSame($oResCard->name, $oResCard->title);

        // unique name
        $oCard = EntityTable::getNewRow();
        $oCard->title = 'test';
        $oCard->save();

        $this->assertNotEmpty($oCard->name);
        $this->assertNotSame($oResCard->name, $oCard->name);

        // long name
        $name = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $oCard = EntityTable::getNewRow();
        $oCard->title = $name;
        $oCard->save();

        $oWCard = EntityTable::getNewRow();
        $oWCard->title = $name;
        $oWCard->save();

        $this->assertNotEmpty($oCard->name);
        $this->assertNotEmpty($oResCard->name);
        $this->assertNotSame($oResCard->name, $oCard->name);

        // dont change
        $curName = $oWCard->name;
        $oWCard->save();
        $this->assertSame($oWCard->name, $curName);
    }

    /**
     * @covers \skewer\components\catalog\model\EntityRow::getModelFromCache()
     */
    public function testGetFTObject()
    {
        $oCard = $this->createCard();
        $oField = $this->createField(['entity' => $oCard->id, 'name' => 'test_name']);

        $oField->setAttr(Attr::SHOW_IN_TAB, 1);

        $oCard->updCache();

        $model = $oCard->getModelFromCache();

        $oRealField = $model->getFiled('test_name');

        $this->assertNotEmpty($oRealField);
        $this->assertSame($oRealField->getName(), 'test_name');
        $this->assertSame($oRealField->getAttr('show_in_tab'), '1');
    }
}
