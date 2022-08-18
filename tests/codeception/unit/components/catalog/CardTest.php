<?php

namespace unit\components\catalog;

use skewer\base\orm\Query;
use skewer\base\section\Tree;
use skewer\components\catalog\Card;
use skewer\components\catalog\Dict;
use skewer\components\catalog\Generator;
use skewer\components\catalog\model\FieldGroupTable;
use skewer\components\catalog\model\GoodsTable;
use skewer\components\catalog\Section;

class CardTest extends \Codeception\Test\Unit
{
    const EXT_CARD = 'dopolnitelnye_parametry';
    private $aCard = [];

    protected function setUp()
    {
        self::clearAllData();

        /* сборка данных и базовой карточки */
        $this->aCard['base_card'] = Generator::genBaseCard();
        /** Расширенная карточка */
        $card = Generator::createExtCard($this->aCard['base_card'], self::EXT_CARD, \Yii::t('data/catalog', 'cart_ext_title', [], \Yii::$app->language));
        $this->aCard['ext_card'] = $card->id;
        $card->updCache();
    }

    protected function setDown()
    {
        if ($oCard = Card::get(self::EXT_CARD)) {
            $oCard->delete();
        }

        if ($oCard = Card::get(Card::DEF_BASE_CARD)) {
            $oCard->delete();
        }

        GoodsTable::removeCard($oCard->id);
    }

    /**
     *@covers \skewer\components\catalog\Card::setGroup()
     */
    public function testSetGroup()
    {
        $oGroup = Card::setGroup('two_group', 'Дополнительная группа');

        $aGroup = ['name' => $oGroup->name,
                   'title' => $oGroup->title,
                   'position' => $oGroup->position, ];

        // Высчитать позицию последней группы
        $aLastPos = \Yii::$app->getDb()->createCommand(
            '
                SELECT MAX(`position`)
                FROM ' . FieldGroupTable::getTableName()
        )->query()->read();

        $aExpGroup = ['name' => 'two_group',
            'title' => 'Дополнительная группа',
            'position' => (int) reset($aLastPos), ];

        $this->assertEquals($aExpGroup, $aGroup, 'группа добавилась с ошибками');
    }

    /**
     *@covers \skewer\components\catalog\Card::getTitle()
     */
    public function testGetTitle()
    {
        $sCardTitle = Card::getTitle($this->aCard['base_card']);
        $this->assertEquals('Базовая карточка', $sCardTitle);

        $sCardTitle = Card::getTitle(Card::DEF_BASE_CARD);
        $this->assertEquals('Базовая карточка', $sCardTitle);

        $sCardTitle = Card::getTitle($this->aCard['ext_card']);
        $this->assertEquals('Дополнительные параметры', $sCardTitle);
        $sCardTitle = Card::getTitle(self::EXT_CARD);
        $this->assertEquals('Дополнительные параметры', $sCardTitle);

        $sCardTitle = Card::getTitle(99999);
        $this->assertEmpty($sCardTitle);
        $sCardTitle = Card::getTitle('abrakadabra');
        $this->assertEmpty($sCardTitle);
    }

    /**
     *@covers \skewer\components\catalog\Card::getName()
     */
    public function testGetName()
    {
        $sCardName = Card::getName($this->aCard['base_card']);
        $this->assertEquals(Card::DEF_BASE_CARD, $sCardName);
        $sCardName = Card::getName($this->aCard['ext_card']);
        $this->assertEquals(self::EXT_CARD, $sCardName);

        $sCardName = Card::getName(99999);
        $this->assertEmpty($sCardName);
    }

    /**
     *@covers \skewer\components\catalog\Card::getId()
     */
    public function testGetId()
    {
        $sCardId = Card::getId(Card::DEF_BASE_CARD);
        $this->assertEquals($this->aCard['base_card'], $sCardId);
        $sCardId = Card::getId(self::EXT_CARD);
        $this->assertEquals($this->aCard['ext_card'], $sCardId);

        $sCardId = Card::getId('abrakadabra');
        $this->assertEmpty($sCardId);
    }

    /**
     *@covers \skewer\components\catalog\Card::getFieldByName()
     */
    public function testGetFieldByName()
    {
        $oField = Card::getField();
        $oField->setData([
            'name' => 'test',
            'title' => 'test',
            'group' => 0,
            'editor' => 'string',
            'prohib_del' => 1,
            'no_edit' => 1,
        ]);
        $oField->entity = $this->aCard['ext_card'];
        $oField->save();

        $this->assertNotEmpty(Card::getFieldByName($this->aCard['ext_card'], 'test'));

        $oField->delete();
    }

    /**
     *@covers \skewer\components\catalog\Card::isDetailHidden()
     */
    public function testIsDetailHidden()
    {
        $oCard = Card::get($this->aCard['ext_card']);
        $oCard->setData(['hide_detail' => 1]);
        $oCard->save();
        $oCard->updCache();

        $root = Tree::addSection(\Yii::$app->sections->main(), 'root');

        $oSection = Tree::addSection($root->id, 'Тестовый раздел');
        Section::setDefCard($oSection->id, $this->aCard['ext_card']);
        $this->assertTrue(Card::isDetailHidden($oSection->id));

        Tree::removeSection($root->id);
    }

    /**
     *@covers \skewer\components\catalog\Card::isDetailHiddenByCard()
     */
    public function testIsDetailHiddenByCard()
    {
        $oCard = Card::get($this->aCard['ext_card']);
        $oCard->setData(['hide_detail' => 1]);
        $oCard->save();
        $oCard->updCache();

        Card::isDetailHiddenByCard($this->aCard['ext_card']);
    }

    /**
     *@covers \skewer\components\catalog\Dict::getDictionaries()
     */
    public function testGetDictionaries()
    {
        $list = Dict::getDictionaries(Card::DEF_GOODS_MODULE);

        foreach ($list as $oCard) {
            $this->assertSame((int) $oCard->type, Card::TypeDictionary);
        }
    }

    /**
     *@covers \skewer\components\catalog\Card::getGoodsCards()
     */
    public function testGetGoodsCards()
    {
        $list = Card::getGoodsCards();

        foreach ($list as $oCard) {
            $this->assertSame((int) $oCard->type, Card::TypeExtended);
        }

        $list = Card::getGoodsCards(true);

        foreach ($list as $oCard) {
            $this->assertNotSame((int) $oCard->type, Card::TypeDictionary);
        }
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
