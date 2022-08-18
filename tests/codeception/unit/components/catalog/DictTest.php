<?php

namespace unit\components\catalog;

use skewer\base\orm\Query;
use skewer\components\catalog\Card;
use skewer\components\catalog\Dict;
use skewer\components\catalog\Generator;
use skewer\components\catalog\model\GoodsTable;

class DictTest extends \Codeception\Test\Unit
{
    const EXT_CARD = 'dopolnitelnye_parametry';
    private $aCard = [];

    /**
     * @throws \Exception
     */
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
     * @covers \skewer\components\catalog\Dict::getUniqAlias
     *
     * @throws \Exception
     */
    public function testUniqAliasDict()
    {
        $oDict = Dict::addDictionary(['title' => 'Справочник'], 'Catalog');

        $iItemId = Dict::setValue($oDict->id, ['title' => 'Китай']);
        $oItem = Dict::getValById($oDict->id, $iItemId);

        $iDuplicateItemId = Dict::setValue($oDict->id, ['title' => 'Китай']);
        $oDuplicateItem = Dict::getValById($oDict->id, $iDuplicateItemId);
        $this->assertNotEquals($oItem[0]['alias'], $oDuplicateItem[0]['alias']);

        Dict::setValue($oDict->id, ['alias' => $oItem[0]['alias']], $iDuplicateItemId);
        $this->assertNotEquals($oItem[0]['alias'], $oDuplicateItem[0]['alias']);

        Dict::removeDict($oDict->id);
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
