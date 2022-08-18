<?php

namespace unit\components\catalog\model;

use skewer\base\orm\Query;
use skewer\components\catalog\Attr;
use skewer\components\catalog\model\EntityTable;
use skewer\components\catalog\model\FieldGroupTable;
use skewer\components\catalog\model\FieldTable;

class FieldRowTest extends \Codeception\Test\Unit
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

    /**
     * @covers \skewer\components\catalog\model\FieldRow::checkUniqueName()
     */
    public function testUniqueName()
    {
        $oCard = $this->createCard();

        $oField = $this->createField(['entity' => $oCard->id, 'title' => '']);

        $this->assertNotEmpty($oField->name);

        $oField2 = $this->createField(['entity' => $oCard->id, 'title' => '']);

        $this->assertNotSame($oField->name, $oField2->name);
    }

    /**
     * @covers \skewer\components\catalog\model\FieldRow::getAttr()
     */
    public function testGetAttr()
    {
        $oCard = $this->createCard();
        $oField = $this->createField(['entity' => $oCard->id]);

        $attr = $oField->getAttr();

        // значения по умолчанию при создании
        $this->assertNotEmpty($attr);
    }

    /**
     * @covers \skewer\components\catalog\model\FieldRow::setAttr()
     */
    public function testSetAttr()
    {
        $oCard = $this->createCard();
        $oField = $this->createField(['entity' => $oCard->id]);

        $attr = $oField->getAttr();

        // значение по умолчанию
        $this->assertSame($attr[Attr::SHOW_IN_LIST]['value'], 1);

        $res = $oField->setAttr(Attr::SHOW_IN_LIST, 0);

        $this->assertTrue($res);

        // новое значение
        $attr = $oField->getAttr();
        $this->assertSame($attr[Attr::SHOW_IN_LIST]['value'], '0');
    }
}
