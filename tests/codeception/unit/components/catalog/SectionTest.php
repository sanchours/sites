<?php

namespace unit\components\catalog;

use skewer\base\orm\Query;
use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\components\catalog\Card;
use skewer\components\catalog\Generator;
use skewer\components\catalog\GoodsRow;
use skewer\components\catalog\model\GoodsTable;
use skewer\components\catalog\model\SectionTable;
use skewer\components\catalog\Section;

/**
 * @covers \skewer\components\catalog\Section
 * Created by PhpStorm.
 * User: Александр
 * Date: 14.09.2015
 * Time: 14:38
 */
class SectionTest extends \Codeception\Test\Unit
{
    const CARD = 'test_card';
    const FIELD = 'test_field';
    const VALUE = 'test_value';
    const VALUE2 = 'test_value2';
    const ALIAS = 'test_alias';

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
        Query::truncateTable('cl_section');
    }

    /**
     * Проверка удаления товаров с разделом
     *
     * @covers \skewer\components\catalog\GoodsRow::removeSection
     */
    public function testRemove()
    {
        // создаем раздел
        $s = Tree::addSection(\Yii::$app->sections->topMenu(), 'news');

        // добавляем товар и привязываем к разделу
        $g = GoodsRow::create(self::CARD);
        $g->setData([
            'title' => 'название123',
            'alias' => self::ALIAS,
            self::FIELD => self::VALUE,
        ]);
        $g->setMainSection($s->id);

        $this->assertNotEmpty($g->save(), 'товар не добавилася');

        $g->setViewSection([$s->id]);

        // проверяем, что данные добавлены
        $this->assertNotEmpty(SectionTable::findOne(
            [
                'section_id' => $s->id,
                'goods_id' => $g->getRowId(),
            ]
        ));
        $this->assertNotEmpty(GoodsTable::findOne(['base_id' => $g->getRowId()]));

        // удаляем раздел
        $s->delete();

        // проверяем, что данные тоже стерлись
        $this->assertEmpty(SectionTable::findOne(
            [
                'section_id' => $s->id,
                'goods_id' => $g->getRowId(),
            ]
        ));
        $this->assertEmpty(GoodsTable::findOne(['section' => $s->id]));
    }

    /*
     * @covers skewer/components/catalog/Section::getList
     */
    public function testGetList()
    {
        $root = Tree::addSection(\Yii::$app->sections->main(), 'root');
        $sect1 = Tree::addSection($root->id, 'visible', Template::getCatalogTemplate());
        Parameters::setParams($sect1->id, 'content', 'object', 'CatalogViewer');
        $sect2 = Tree::addSection($root->id, 'hidden', Template::getCatalogTemplate(), '', Visible::HIDDEN_FROM_PATH);
        Parameters::setParams($sect2->id, 'content', 'object', 'CatalogViewer');
        $sect3 = Tree::addSection($root->id, 'standart');

        $this->assertTrue((bool) array_key_exists($sect1->id, Section::getList()));
        $this->assertTrue((bool) array_key_exists($sect2->id, Section::getList()));
        $this->assertTrue((bool) array_key_exists($sect1->id, Section::getList(true)));

        $this->assertFalse((bool) array_key_exists($sect2->id, Section::getList(true)));
        $this->assertFalse((bool) array_key_exists($sect3->id, Section::getList()));
        $this->assertFalse((bool) array_key_exists($sect3->id, Section::getList(true)));

        Tree::removeSection($root->id);
    }

    /*
     * @covers skewer/components/catalog/Section::removeGoods
     */
    public function testRemoveGoods()
    {
        $root = Tree::addSection(\Yii::$app->sections->main(), 'root');
        $sect1 = Tree::addSection($root->id, 'visible', Template::getCatalogTemplate());
        Parameters::setParams($sect1->id, 'content', 'object', 'CatalogViewer');
        $oGood = GoodsRow::create(self::CARD);
        $oGood->setData(['title' => 'название123']);
        $oGood->save();
        $oGood->setViewSection([$sect1->id]);

        $oCard = Card::get(Card::DEF_BASE_CARD);

        $this->assertNotEmpty(in_array($sect1->id, $oGood->getViewSection()));
        Section::removeGoods($sect1->id, $oGood->getRowId(), $oCard->id);
        if (is_array($oGood->getViewSection())) {
            $this->assertEmpty(in_array($sect1->id, $oGood->getViewSection()));
        } else {
            $this->assertEmpty($oGood->getViewSection());
        }

        $oGood->delete();
        $root->delete();
    }

    /*
     * @covers skewer/components/catalog/Section::setMain4Goods
     */
    public function testSetMain4Goods()
    {
        $root = Tree::addSection(\Yii::$app->sections->main(), 'root');
        $sect1 = Tree::addSection($root->id, 'visible', Template::getCatalogTemplate());
        $oGood = GoodsRow::create(self::CARD);
        $oGood->setData(['title' => 'название123']);
        $oGood->save();
        $oGood->setMainSection($sect1->id);

        $this->assertEquals($sect1->id, $oGood->getMainSection());
        $sect2 = Tree::addSection($root->id, 'visible2', Template::getCatalogTemplate());
        Section::setMain4Goods($oGood->getRowId(), $sect2->id);
        $this->assertEquals($sect2->id, $oGood->getMainSection());

        $oGood->delete();
        $root->delete();
    }

    /*
     * @covers skewer/components/catalog/Section::getCardList
     */
    public function testGetCardList()
    {
        $root = Tree::addSection(\Yii::$app->sections->main(), 'root');
        $sect1 = Tree::addSection($root->id, 'visible', Template::getCatalogTemplate());

        $oBaseCard = Card::get(Card::DEF_BASE_CARD);
        $oExtCard = Card::get(self::CARD);
        $oExtCard2 = Generator::createExtCard($oBaseCard->id, 'test_card_2');
        $oExtCard2->updCache();

        $oGood = GoodsRow::create(self::CARD);
        $oGood->setData(['title' => 'название123']);
        $oGood->save();
        $oGood->setViewSection([$sect1->id]);

        $oGood2 = GoodsRow::create(self::CARD);
        $oGood2->setData(['title' => 'название123']);
        $oGood2->save();
        $oGood2->setViewSection([$sect1->id]);

        $this->assertTrue(in_array($oExtCard->id, Section::getCardList([$sect1->id])));
        $this->assertFalse(in_array($oExtCard2->id, Section::getCardList([$sect1->id])));

        $oGood3 = GoodsRow::create('test_card_2');
        $oGood3->setData(['title' => 'название123']);
        $oGood3->save();
        $oGood3->setViewSection([$sect1->id]);

        $this->assertTrue(in_array($oExtCard->id, Section::getCardList([$sect1->id])));
        $this->assertTrue(in_array($oExtCard2->id, Section::getCardList([$sect1->id])));

        $root->delete();
        $oGood->delete();
        $oGood2->delete();
        $oGood3->delete();

        GoodsTable::removeCard($oExtCard2->id);
        $oExtCard2->delete();
    }
}
