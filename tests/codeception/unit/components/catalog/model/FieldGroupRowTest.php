<?php

namespace unit\components\catalog\model;

use skewer\base\orm\Query;
use skewer\components\catalog\model\EntityTable;
use skewer\components\catalog\model\FieldGroupTable;
use skewer\components\catalog\model\FieldTable;

class FieldGroupRowTest extends \Codeception\Test\Unit
{
    protected function clearAllData()
    {
        Query::SQL('TRUNCATE c_entity');
        Query::SQL('TRUNCATE c_field');
        Query::SQL('TRUNCATE c_validator');
        Query::SQL('TRUNCATE c_field_attr');
        Query::SQL('TRUNCATE c_field_group');
    }

    protected function setUp()
    {
        $this->clearAllData();
    }

    protected function setDown()
    {
    }

    protected function createCard()
    {
        $oCard = EntityTable::getNewRow();
        $oCard->title = 'test';
        $oCard->save();

        return $oCard;
    }

    protected function createGroup($data = [])
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

    /**
     * @covers \skewer\components\catalog\model\FieldGroupRow::checkUniqueName
     */
    public function testGroupUniqueName()
    {
        $oGroup = $this->createGroup(['title' => '']);

        $this->assertNotEmpty($oGroup->name);

        $oGroup2 = $this->createGroup(['title' => '']);

        $this->assertNotSame($oGroup->name, $oGroup2->name);
    }

    /**
     * @covers \skewer\components\catalog\model\FieldGroupRow::getFields
     */
    public function testGetFields()
    {
        $oCard = $this->createCard();
        $oCard2 = $this->createCard();

        $oGroup = $this->createGroup(['name' => 'g1']);

        $this->createField(['entity' => $oCard->id, 'group' => $oGroup->id, 'title' => 'f1']);
        $this->createField(['entity' => $oCard2->id, 'group' => 0, 'title' => 'f4']);
        $this->createField(['entity' => $oCard2->id, 'group' => $oGroup->id, 'title' => 'f2']);
        $this->createField(['entity' => $oCard2->id, 'group' => $oGroup->id, 'title' => 'f3']);

        $aFields = $oGroup->getFields();

        $this->assertNotEmpty($aFields);
        $this->assertSame(count($aFields), 3);
        $this->assertSame($aFields[0]->name, 'f1');
        $this->assertSame($aFields[1]->name, 'f2');
    }

    /**
     * @covers \skewer\components\catalog\model\FieldGroupRow::checkPos
     */
    public function testPositions()
    {
        $oGroup = $this->createGroup();
        // ошибка при первичной генерации (1)
        $this->assertNotEmpty($oGroup->position);

        $oGroup2 = $this->createGroup();

        $this->assertNotEmpty($oGroup2->position);
        // новая группа должна быть с большим весом
        $this->assertLessThan($oGroup2->position, $oGroup->position);

        $oGroup->delete();

        $oGroup3 = $this->createGroup();
        $this->assertNotEmpty($oGroup3->position);
        // новая группа должна быть с большим весом, даже если есть "дырки"
        $this->assertLessThan($oGroup3->position, $oGroup2->position);
    }

    /**
     * @covers \skewer\components\catalog\model\FieldGroupRow::delete
     */
    public function testDelete()
    {
        $oCard = $this->createCard();
        $oGroup = $this->createGroup();

        $this->createField(['entity' => $oCard->id, 'group' => $oGroup->id, 'title' => 'testfield']);

        $res = $oGroup->delete();

        $this->assertTrue($res);

        $oField = FieldTable::findOne(['name' => 'testfield']);

        // после удаления группы удаляютсы все связи
        $this->assertSame($oField->group, '0');
    }
}
