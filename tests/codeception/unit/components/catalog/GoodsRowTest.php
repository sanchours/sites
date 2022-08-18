<?php

namespace unit\components\catalog;

use skewer\base\ft\Cache;
use skewer\base\ft\Editor;
use skewer\base\ft\Exception;
use skewer\base\ft\Relation;
use skewer\base\orm\Query;
use skewer\components\catalog\Attr;
use skewer\components\catalog\Card;
use skewer\components\catalog\Collection;
use skewer\components\catalog\Generator;
use skewer\components\catalog\GoodsRow;
use skewer\components\catalog\model\GoodsTable;
use skewer\components\gallery\models\Profiles;
use skewer\components\gallery\Profile;

/**
 * @covers \skewer\components\catalog\GoodsRow
 */
class GoodsRowTest extends \Codeception\Test\Unit
{
    const CARD = 'test_card';
    const FIELD = 'test_field';
    const FIELD2 = 'test_field2';
    const FIELD3 = 'test_field3';
    const VALUE = 'test_value';
    const VALUE2 = 'test_value2';
    const ALIAS = 'test_alias';

    public function providerCheckAlias()
    {
        return [
            ['Название', ''],
        ];
    }

    protected function setUp()
    {
        $this->clearAllData();
        $this->createCard();
    }

    /* #65144_removeCard */
    protected function tearDown()
    {
        if ($oCard = Card::get(self::CARD)) {
            $oCard->delete();
        }

        GoodsTable::removeCard($oCard->id);

        if ($oCard = Card::get(Card::DEF_BASE_CARD)) {
            $oCard->delete();
        }

        GoodsTable::removeCard($oCard->id);
    }

    public function createCard()
    {
        $base = Generator::genBaseCard();

        $card = Generator::createExtCard($base, self::CARD);
        Generator::createField(
            $card->id,
            [
                'name' => self::FIELD,
                'editor' => 'string',
            ]
        );
        $card->updCache();
    }

    protected function clearAllData()
    {
        Query::truncateTable('c_entity');
        Query::truncateTable('c_field');
        Query::truncateTable('c_validator');
        Query::truncateTable('c_field_attr');
        Query::truncateTable('c_field_group');
    }

    /**
     * @covers \skewer\components\catalog\GoodsRow::get
     * @covers \skewer\components\catalog\GoodsRow::getByAlias
     * @covers \skewer\components\catalog\GoodsRow::getByFields
     */
    public function testGet()
    {
        $oGoods = GoodsRow::create(self::CARD);

        $oGoods->setData([
            'title' => 'название123',
            'alias' => self::ALIAS,
            self::FIELD => self::VALUE,
        ]);
        $oGoods->save();

        // get
        $oTestGoods = GoodsRow::get($oGoods->getRowId(), self::CARD);

        $this->assertNotEmpty($oTestGoods);
        $this->assertSame($oTestGoods->getRowId(), $oGoods->getRowId());
        //$this->assertSame( $oTestGoods->getFields(), $oGoods->getFields() );

        // getByAlias
        $oTestGoods = GoodsRow::getByAlias(self::ALIAS, Card::DEF_BASE_CARD);

        $this->assertNotEmpty($oTestGoods);
        $this->assertSame($oTestGoods->getRowId(), $oGoods->getRowId());
        //$this->assertSame( $oTestGoods->getFields(), $oGoods->getFields() );

        // getByFields
        $oTestGoods = GoodsRow::getByFields([self::FIELD => self::VALUE], self::CARD);

        $this->assertNotEmpty($oTestGoods);
        $this->assertSame($oTestGoods->getRowId(), $oGoods->getRowId());
        //$this->assertSame( $oTestGoods->getFields(), $oGoods->getFields() );
    }

    /**
     * @covers \skewer\components\catalog\GoodsRow::getData
     */
    public function testData()
    {
        $oGoods = GoodsRow::create(self::CARD);

        $oGoods->setData(['alias' => self::ALIAS, self::FIELD => self::VALUE]);
        $oGoods->save();

        $aData = $oGoods->getData();

        $this->assertNotEmpty($aData);

        $this->assertSame($aData['alias'], self::ALIAS);
        $this->assertSame($aData[self::FIELD], self::VALUE);

        $oGoods->setData([self::FIELD => self::VALUE2]);
        $oGoods->save();

        $aData = $oGoods->getData();

        $this->assertSame($aData[self::FIELD], self::VALUE2);
    }

    public function testLinkSections()
    {
        // setViewSection/getViewSection
    }

    public function testLinkMainSection()
    {
        // getMainSection/setMainSection
    }

    public function testSave()
    {
    }

    public function testDelete()
    {
    }

    public function testAlias()
    {
    }

    /**
     * @covers \skewer\components\catalog\GoodsRow::create
     */
    public function testCreate()
    {
        $oGood = GoodsRow::create(self::CARD);
        $oExtCard = Card::get(self::CARD);
        $oBaseCard = Card::get($oExtCard->in_base);

        $this->assertEquals($oGood->getBaseCardName(), $oBaseCard->name);
        $this->assertEquals($oGood->getExtCardName(), $oExtCard->name);
    }

    /**
     * @covers \skewer\components\catalog\GoodsRow::create
     */
    public function testCreateException()
    {
        $this->expectException(\Exception::class);

        GoodsRow::create(Card::DEF_BASE_CARD);
    }

    /**
     * @covers \skewer\components\catalog\GoodsRow::getByAlias()
     */
    public function testGetByAlias()
    {
        $oGood = GoodsRow::create(self::CARD);
        $oGood->setData(['title' => 'Наименование товара']);
        $oGood->save();
        $aData = $oGood->getData();

        $oActualGood = GoodsRow::getByAlias($aData['alias'], Card::DEF_BASE_CARD);
        $this->assertEquals($oGood->getRowId(), $oActualGood->getRowId());
        $oActualGood = GoodsRow::getByAlias($aData['alias'], self::CARD);
        $this->assertEquals($oGood->getRowId(), $oActualGood->getRowId());

        $oGood->delete();

        $this->assertFalse(GoodsRow::getByAlias('abracadabra', self::CARD));

        $this->assertEmpty(GoodsRow::getByAlias($aData['alias'], Card::DEF_BASE_CARD));
        $this->assertEmpty(GoodsRow::getByAlias($aData['alias'], self::CARD));
    }

    /**
     * @covers \skewer\components\catalog\GoodsRow::getByAlias()
     */
    public function testGetByAliasException()
    {
        $this->expectException(\Exception::class);

        GoodsRow::getByAlias('abracadabra', 'abracadabra');
    }

    /**
     * @covers \skewer\components\catalog\GoodsRow::setField()
     */
    public function testSetField()
    {
        $value = 'тестовая строка';

        $oExtCard = Card::get(self::CARD);
        Generator::createField(
            $oExtCard->id,
            [
                'name' => self::FIELD2,
                'editor' => 'string',
            ]
        );
        $oExtCard->updCache();

        $oBaseCard = Card::get(Card::DEF_BASE_CARD);
        Generator::createField(
            $oBaseCard->id,
            [
                'name' => self::FIELD2,
                'editor' => 'string',
            ]
        );
        Generator::createField(
            $oBaseCard->id,
            [
                'name' => self::FIELD3,
                'editor' => 'string',
            ]
        );
        $oBaseCard->updCache();

        $oGood = GoodsRow::create(self::CARD);
        $oGood->setData(['title' => 'Название товара',
                        self::FIELD2 => $value,
                        self::FIELD3 => $value, ]);
        $oGood->save();

        $aBaseCardGood = Query::SelectFrom(Card::getTablePreffix(Card::TypeBasic) . $oBaseCard->name)->where('id', $oGood->getRowId())->asArray()->getOne();
        $aExtCardGood = Query::SelectFrom(Card::getTablePreffix(Card::TypeExtended) . $oExtCard->name)->where('id', $oGood->getRowId())->asArray()->getOne();

        $this->assertEquals($value, $aBaseCardGood[self::FIELD2]);
        $this->assertEquals($value, $aExtCardGood[self::FIELD2]);

        $this->assertEquals($value, $aBaseCardGood[self::FIELD3]);
        $this->assertArrayNotHasKey(self::FIELD3, $aExtCardGood);

        $oGood->delete();
    }

    /**
     * @covers \skewer\components\catalog\GoodsRow::getData()
     */
    public function testGetData()
    {
        $oExtCard = Card::get(self::CARD);
        Generator::createField(
            $oExtCard->id,
            [
                'name' => self::FIELD2,
                'editor' => 'string',
            ]
        );
        $oExtCard->updCache();

        $oBaseCard = Card::get(Card::DEF_BASE_CARD);
        Generator::createField(
            $oBaseCard->id,
            [
                'name' => self::FIELD2,
                'editor' => 'string',
            ]
        );

        $oBaseCard->updCache();

        $value = 'тестовая строка';
        $value2 = 'test data';

        $oGood = GoodsRow::create(self::CARD);
        $oGood->setData(['title' => 'Название товара',
            self::FIELD => $value,
            self::FIELD2 => $value, ]);
        $oGood->save();

        $aData = $oGood->getData();

        $this->assertEquals($value, $aData[self::FIELD]);
        $this->assertEquals($value, $aData[self::FIELD2]);

        Query::UpdateFrom(Card::getTablePreffix(Card::TypeBasic) . $oBaseCard->name)->set(self::FIELD2, $value2)->get();
        $oGood = GoodsRow::get($oGood->getRowId());
        $aData = $oGood->getData();
        $aBaseCardGood = Query::SelectFrom(Card::getTablePreffix(Card::TypeBasic) . $oBaseCard->name)->where('id', $oGood->getRowId())->asArray()->getOne();

        $this->assertNotEquals($value, $aBaseCardGood[self::FIELD2]);
        $this->assertEquals($value2, $aBaseCardGood[self::FIELD2]);
        $this->assertEquals($value, $aData[self::FIELD2]);

        $this->assertArrayNotHasKey(self::FIELD, $aBaseCardGood);

        $oGood->delete();
    }

    /**
     * @covers \skewer\components\catalog\GoodsRow::setUpdDate()
     */
    public function testSetUpdDate()
    {
        $oGood = GoodsRow::create(self::CARD);
        $oGood->setData(['title' => 'Название товара']);
        $oGood->save();

        $oBaseCard = Card::get(Card::DEF_BASE_CARD);

        Query::UpdateFrom(GoodsTable::getTableName())
            ->set('__upd_date', '2017-12-05 12:46:47')
            ->where('base_id', $oGood->getRowId())
            ->where('base_card_id', $oBaseCard->id)
            ->get();

        $sOldDate = GoodsTable::getChangeDate($oGood->getRowId(), $oBaseCard->id);
        $oGood->setData([self::FIELD => 'Название товара']);
        $oGood->save();
        $sDate = GoodsTable::getChangeDate($oGood->getRowId(), $oBaseCard->id);

        $this->assertNotEquals($sOldDate, $sDate);

        $oGood->delete();
    }

    /**
     * @covers \skewer\components\catalog\GoodsRow::createLink()
     */
    public function testCreateLink()
    {
        $oBaseCard = Card::get(Card::DEF_BASE_CARD);
        $oExtCard = Card::get(self::CARD);

        $oGood = GoodsRow::create(self::CARD);
        $oGood->setData(['title' => 'test_title']);
        $oGood->save();

        $iGoodTableRowCount = Query::SelectFrom(GoodsTable::getTableName())->where('base_id', $oGood->getRowId())->getCount();
        $aGoodTableRow = Query::SelectFrom(GoodsTable::getTableName())->where('base_id', $oGood->getRowId())->asArray()->getOne();
        $this->assertTrue($iGoodTableRowCount == 1 ? true : false);

        $this->assertEquals($oBaseCard->id, $aGoodTableRow['base_card_id']);
        $this->assertEquals($oBaseCard->name, $aGoodTableRow['base_card_name']);
        $this->assertEquals($oExtCard->id, $aGoodTableRow['ext_card_id']);
        $this->assertEquals($oExtCard->name, $aGoodTableRow['ext_card_name']);

        $oGood->delete();
    }

    /**
     * @covers \skewer\components\catalog\GoodsRow::removeLink()
     */
    public function testRemoveLink()
    {
        $oGood = GoodsRow::create(self::CARD);
        $oGood->setData(['title' => 'test_title']);
        $oGood->save();

        $oGood->delete();

        $iGoodTableRowCount = Query::SelectFrom(GoodsTable::getTableName())->where('base_id', $oGood->getRowId())->getCount();
        $this->assertTrue($iGoodTableRowCount == 0 ? true : false);
    }

    /**
     * #63792_braking_test.
     *
     * @covers \skewer\components\catalog\GoodsRow::fillLinkFields()
     */
    public function testFillLinkFields()
    {
        //необходимо дождаться разбора функций справочника
    }

    /**
     * @covers \skewer\base\ft\model\Field::updLinkRow
     *
     * @throws Exception
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function testUpdLinkRow()
    {
        Cache::clearCache();

        $oExtCard = Card::get(self::CARD);

        $oProfile = new Profiles([
            'type' => Profile::TYPE_CATALOG4COLLECTION,
            'title' => 'Профиль коллекции',
            'active' => 1,
        ]);
        $oProfile->save();

        $oCollectionTech = Collection::addCollection(['title' => 'Технология(коллекция)'], $oProfile->id);
        $oMagicTable = Cache::getMagicTable($oCollectionTech->id);

        $iLED_COL = $oMagicTable->getNewRow(['title' => 'LED', 'alias' => 'led'])->save();
        $iOLED_COL = $oMagicTable->getNewRow(['title' => 'OLED', 'alias' => 'oled'])->save();
        $iQLED_COL = $oMagicTable->getNewRow(['title' => 'QLED', 'alias' => 'qled'])->save();

        $sNameField = 'tech_multicollection';
        $sTable = 'cr_' . self::CARD . '__' . $sNameField;

        $iIdField = Generator::createField(
            $oExtCard->id,
            [
                'name' => $sNameField,
                'editor' => Editor::MULTICOLLECTION,
                'link_id' => $oCollectionTech->id,
                'attr' => [
                    Attr::ACTIVE => 1,
                    Attr::SHOW_IN_FILTER => 1,
                ],
            ]
        );

        $oExtCard->updCache();

        $oGood = GoodsRow::create(self::CARD);
        $oGood->setData(['title' => 'test_title']);
        $oGood->save();

        //проверяем что таблица пустая
        $count = (new \yii\db\Query())->from($sTable)->count();
        $this->assertEquals(0, $count);

        //запишем одно значение
        $oGood->setData([$sNameField => $iLED_COL]);
        $oGood->save();

        //проверим что в таблице только одно значение
        $count = (new \yii\db\Query())->from($sTable)->count();
        $item = (new \yii\db\Query())->from($sTable)
            ->where([Relation::INNER_FIELD => $oGood->getRowId(),
                     Relation::EXTERNAL_FIELD => $iLED_COL,
                     Relation::SORT_FIELD => 1,
            ])
            ->all();

        $this->assertEquals(1, $count);
        $this->assertCount(1, $item);

        //поменяем одно значение на другое одно значение
        $oGood->setData([$sNameField => $iOLED_COL]);
        $oGood->save();
        //проверим что в таблице только одно значение
        $count = (new \yii\db\Query())->from($sTable)->count();
        $item = (new \yii\db\Query())->from($sTable)
            ->where([Relation::INNER_FIELD => $oGood->getRowId(),
                Relation::EXTERNAL_FIELD => $iOLED_COL,
                Relation::SORT_FIELD => 1,
            ])
            ->all();

        $this->assertEquals(1, $count);
        $this->assertCount(1, $item);

        //добавим два новых значения
        $oGood->setData([$sNameField => [$iOLED_COL, $iLED_COL, $iQLED_COL]]);
        $oGood->save();
        //проверим что в таблице только нужные поля
        $count = (new \yii\db\Query())->from($sTable)->count();
        $item = (new \yii\db\Query())->from($sTable)
            ->where([Relation::INNER_FIELD => $oGood->getRowId(),
                Relation::EXTERNAL_FIELD => $iOLED_COL,
                Relation::SORT_FIELD => 1,
            ])
            ->orWhere([Relation::INNER_FIELD => $oGood->getRowId(),
                Relation::EXTERNAL_FIELD => $iLED_COL,
                Relation::SORT_FIELD => 1,
            ])
            ->orWhere([Relation::INNER_FIELD => $oGood->getRowId(),
                Relation::EXTERNAL_FIELD => $iQLED_COL,
                Relation::SORT_FIELD => 1,
            ])
            ->all();

        $this->assertEquals(3, $count);
        $this->assertCount(3, $item);

        //создадим новый товар
        $oGood2 = GoodsRow::create(self::CARD);
        $oGood2->setData(['title' => 'test_title']);
        $oGood2->save();

        //добавим ему несколько связей
        $oGood2->setData([$sNameField => [$iOLED_COL, $iQLED_COL]]);
        $oGood2->save();

        //удалим все связи у первого товара
        $oGood->setData([$sNameField => []]);
        $oGood->save();

        //проверим состояние таблицы
        $count = (new \yii\db\Query())->from($sTable)->count();
        $item = (new \yii\db\Query())->from($sTable)
            ->where([Relation::INNER_FIELD => $oGood2->getRowId(),
                Relation::EXTERNAL_FIELD => $iOLED_COL,
                Relation::SORT_FIELD => 2,
            ])

            ->orWhere([Relation::INNER_FIELD => $oGood2->getRowId(),
                Relation::EXTERNAL_FIELD => $iQLED_COL,
                Relation::SORT_FIELD => 2,
            ])
            ->all();

        $this->assertEquals(2, $count);
        $this->assertCount(2, $item);

        $oField = Card::getField($iIdField);
        $oField->delete();
        $oProfile->delete();
    }
}
