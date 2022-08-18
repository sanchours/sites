<?php

namespace unit\components\filters;

use skewer\base\ft\Cache;
use skewer\base\ft\Editor;
use skewer\base\section\models\ParamsAr;
use skewer\base\section\models\TreeSection;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\base\site\Layer;
use skewer\components\catalog;
use skewer\components\catalog\Card;
use skewer\components\filters\FilterPrototype;
use skewer\components\gallery\models\Profiles;
use skewer\components\gallery\Profile;
use yii\helpers\ArrayHelper;

class FilterTest extends \Codeception\Test\Unit
{
    private $aProfiles = [];
    private $aSections = [];

    private $iFilterSection;
    private $iExtCard;

    private $aAliasesGoods = [];

    protected function setUp()
    {
        $aCards = array_merge(
            ArrayHelper::getColumn(catalog\Dict::getDictionaries(Layer::CATALOG), 'id'),
            ArrayHelper::getColumn(catalog\Collection::getCollections(), 'id')
        );

        foreach ($aCards as $iCard) {
            if ($oCard = Card::get($iCard)) {
                $oCard->delete();
            }
        }

        catalog\Entity::clearCache();

        $oDictBrands = catalog\Dict::addDictionary(['title' => 'Бренды'], Layer::CATALOG);

        $oMagicTable = Cache::getMagicTable($oDictBrands->id);
        $iLg = $oMagicTable->getNewRow(['title' => 'LG', 'alias' => 'lg'])->save();
        $iSamsung = $oMagicTable->getNewRow(['title' => 'Samsung', 'alias' => 'samsung'])->save();
        $iSony = $oMagicTable->getNewRow(['title' => 'Sony', 'alias' => 'sony'])->save();

        $oDicTech = catalog\Dict::addDictionary(['title' => 'Технология'], Layer::CATALOG);

        $oMagicTable = Cache::getMagicTable($oDicTech->id);
        $iLED = $oMagicTable->getNewRow(['title' => 'LED', 'alias' => 'led'])->save();
        $iOLED = $oMagicTable->getNewRow(['title' => 'OLED', 'alias' => 'oled'])->save();

        $oProfile = new Profiles([
            'type' => Profile::TYPE_CATALOG4COLLECTION,
            'title' => 'Профиль коллекции',
            'active' => 1,
        ]);
        $oProfile->save();
        $this->aProfiles[] = $oProfile->id;

        $oCollectionTech = catalog\Collection::addCollection(['title' => 'Технология(коллекция)'], $oProfile->id);
        $oMagicTable = Cache::getMagicTable($oCollectionTech->id);
        $iLED_COL = $oMagicTable->getNewRow(['title' => 'LED', 'alias' => 'led'])->save();
        $iOLED_COL = $oMagicTable->getNewRow(['title' => 'OLED', 'alias' => 'oled'])->save();
        $iQLED_COL = $oMagicTable->getNewRow(['title' => 'QLED', 'alias' => 'qled'])->save();

        $oBaseCard = Card::get(Card::DEF_BASE_CARD);
        if (!$oBaseCard) {
            catalog\Generator::genBaseCard();
        }

        $oBaseCard = Card::get(Card::DEF_BASE_CARD);

        foreach ($oBaseCard->getFields() as $filed) {
            $filed->setAttr(catalog\Attr::SHOW_IN_FILTER, 1);
        }

        // Удадяем из базовой карточки справочники и коллекции
        $this->deleteDictsAndCollectionsFromCard($oBaseCard);

        $oExtCard = catalog\Generator::createExtCard($oBaseCard->id, 'Телевизоры', 'Телевизоры');

        $this->iExtCard = $oExtCard->id;
        catalog\Generator::createField(
            $oExtCard->id,
            [
                'name' => 'brands',
                'editor' => Editor::MULTISELECT,
                'link_id' => $oDictBrands->id,
                'attr' => [
                    catalog\Attr::ACTIVE => 1,
                    catalog\Attr::SHOW_IN_FILTER => 1,
                ],
            ]
        );

        catalog\Generator::createField(
            $oExtCard->id,
            [
                'name' => 'sale_m',
                'editor' => Editor::CHECK,
                'attr' => [
                    catalog\Attr::ACTIVE => 1,
                    catalog\Attr::SHOW_IN_FILTER => 1,
                ],
            ]
        );

        catalog\Generator::createField(
            $oExtCard->id,
            [
                'name' => 'price_m',
                'editor' => Editor::MONEY,
                'attr' => [
                    catalog\Attr::ACTIVE => 1,
                    catalog\Attr::SHOW_IN_FILTER => 1,
                ],
            ]
        );

        catalog\Generator::createField(
            $oExtCard->id,
            [
                'name' => 'tech_m',
                'editor' => Editor::MULTISELECT,
                'link_id' => $oDicTech->id,
                'attr' => [
                    catalog\Attr::ACTIVE => 1,
                    catalog\Attr::SHOW_IN_FILTER => 1,
                ],
            ]
        );

        catalog\Generator::createField(
            $oExtCard->id,
            [
                'name' => 'tech_collection',
                'editor' => Editor::COLLECTION,
                'link_id' => $oCollectionTech->id,
                'attr' => [
                    catalog\Attr::ACTIVE => 1,
                    catalog\Attr::SHOW_IN_FILTER => 1,
                ],
            ]
        );

        catalog\Generator::createField(
            $oExtCard->id,
            [
                'name' => 'tech_multicollection',
                'editor' => Editor::MULTICOLLECTION,
                'link_id' => $oCollectionTech->id,
                'attr' => [
                    catalog\Attr::ACTIVE => 1,
                    catalog\Attr::SHOW_IN_FILTER => 1,
                ],
            ]
        );

        catalog\Generator::createField(
            $oExtCard->id,
            [
                'name' => 'html_text',
                'editor' => Editor::TEXT,
                'attr' => [
                    catalog\Attr::ACTIVE => 1,
                    catalog\Attr::SHOW_IN_FILTER => 1,
                ],
            ]
        );

        catalog\Generator::createField(
            $oExtCard->id,
            [
                'name' => 'wyswyg_text',
                'editor' => Editor::WYSWYG,
                'attr' => [
                    catalog\Attr::ACTIVE => 1,
                    catalog\Attr::SHOW_IN_FILTER => 1,
                ],
            ]
        );

        catalog\Generator::createField(
            $oExtCard->id,
            [
                'name' => 'string_text',
                'editor' => Editor::STRING,
                'attr' => [
                    catalog\Attr::ACTIVE => 1,
                    catalog\Attr::SHOW_IN_FILTER => 1,
                ],
            ]
        );

        $oExtCard->updCache();

        $iCatalogTpl = Template::getTemplateIdForModule('CatalogViewer');
        $oSection = Tree::addSection(\Yii::$app->sections->leftMenu(), 'Страница с фильтром', $iCatalogTpl);
        $this->aSections[] = $oSection->id;
        $this->iFilterSection = $oSection->id;

        $aGoodsData = [
            [
                'title' => 'LG 5000',
                'alias' => 'lg_5000',
                'price_m' => 5000,
                'brands' => $iLg,
                'sale_m' => 1,
                'tech_m' => [$iOLED, $iLED],
                'tech_collection' => $iOLED_COL,
                'tech_multicollection' => [$iOLED_COL, $iLED_COL],
                'html_text' => 'LG 5000',
                'wyswyg_text' => 'LG 5000',
                'string_text' => 'LG 5000',
            ],
            [
                'title' => 'LG 6000',
                'alias' => 'lg_6000',
                'price_m' => 6000,
                'brands' => $iLg,
                'sale_m' => 1,
                'tech_m' => [$iLED],
                'tech_collection' => $iQLED_COL,
                'tech_multicollection' => [$iLED_COL],
                'html_text' => 'LG 6000',
                'wyswyg_text' => 'LG 6000',
                'string_text' => 'LG 6000',
            ],
            [
                'title' => 'SAMSUNG 6500',
                'alias' => 'samsung_6500',
                'price_m' => 6500,
                'brands' => $iSamsung,
                'sale_m' => 0,
                'tech_m' => [$iOLED],
                'tech_collection' => $iLED_COL,
                'tech_multicollection' => [$iOLED_COL, $iQLED_COL],
                'html_text' => 'SAMSUNG 6500',
                'wyswyg_text' => 'SAMSUNG 6500',
                'string_text' => 'SAMSUNG 6500',
            ],
            [
                'title' => '\_%товар1%',
                'alias' => 'tovar1',
                'price_m' => 7000,
                'html_text' => '\_%товар1%',
                'wyswyg_text' => '\_%товар1%',
                'string_text' => '\_%товар1%',
            ],
        ];

        $this->aAliasesGoods = [];
        foreach ($aGoodsData as $aGood) {
            $row = catalog\GoodsRow::create($oExtCard->id);
            $row->setData($aGood);
            $row->save();
            $row->setViewSection([$this->iFilterSection]);

            $this->aAliasesGoods[ArrayHelper::getValue($row->getData(), 'alias')] = $row->getRowId();
        }
    }

    public function providerFilterConditions()
    {
        return [
            ['string_text=lg 5000', ['lg_5000']],
            ['brands=lg', ['lg_5000', 'lg_6000']],
            ['brands=lg,samsung', ['lg_5000', 'lg_6000', 'samsung_6500']],
            ['sale_m=yes', ['lg_5000', 'lg_6000']],
            ['tech_m=oled,led', ['lg_5000', 'lg_6000', 'samsung_6500']],
            ['tech_m=led', ['lg_5000', 'lg_6000']],
            ['tech_m=oled', ['lg_5000', 'samsung_6500']],
            ['price_m=5000,6500', ['lg_5000', 'lg_6000', 'samsung_6500']],
            ['price_m=5000,6000', ['lg_5000', 'lg_6000']],
            ['price_m=5000,5000', ['lg_5000']],
            ['price_m=0,100', []],
            ['tech_collection=oled', ['lg_5000']],
            ['tech_collection=oled,qled', ['lg_5000', 'lg_6000']],
            ['tech_collection=', ['lg_5000', 'lg_6000', 'samsung_6500', 'tovar1']],
            ['tech_multicollection=oled', ['lg_5000', 'samsung_6500']],
            ['tech_multicollection=led,qled', ['lg_5000', 'lg_6000', 'samsung_6500']],
            ['html_text=lg', ['lg_5000', 'lg_6000']],
            ['html_text=lg 5000', ['lg_5000']],
            ['wyswyg_text=lg', ['lg_5000', 'lg_6000']],
            ['wyswyg_text=lg 5000', ['lg_5000']],
            ['string_text=lg', ['lg_5000', 'lg_6000']],

            //Проверка экранирования
            ['wyswyg_text=\_%товар1', ['tovar1']],
            ['title=\_%товар1', ['tovar1']],
        ];
    }

    /**
     * @dataProvider providerFilterConditions
     * @covers \skewer\components\catalog\GoodsSelector::applyFilter
     *
     * @param mixed $sIn
     * @param mixed $fOut
     */
    public function testApplyFilter($sIn, $fOut)
    {
        $oFilter = FilterPrototype::getInstanceByCard($this->iExtCard, $this->iFilterSection, $sIn, FilterPrototype::FILTER_TYPE_INDEX);
        $oSelector = catalog\GoodsSelector::getList($this->iExtCard);
        $oSelector->applyFilter($oFilter);
        $aOut = $oSelector->parse();

        $aActual = ArrayHelper::getColumn($aOut, static function ($element) {
            return (int) $element['id'];
        }, []);

        $aExpected = [];
        foreach ($fOut as $item) {
            $aExpected[] = $this->aAliasesGoods[$item];
        }

        $this->assertSame($aExpected, $aActual);
    }

    public function tearDown()
    {
        Profiles::deleteAll(['id' => $this->aProfiles]);
        TreeSection::deleteAll(['id' => $this->aSections]);

        $aCards = array_merge(
            ArrayHelper::getColumn(catalog\Dict::getDictionaries(Layer::CATALOG), 'id'),
            ArrayHelper::getColumn(catalog\Collection::getCollections(), 'id'),
            [$this->iExtCard]
        );

        foreach ($this->aAliasesGoods as $iGoodId) {
            catalog\Api::deleteGoods($iGoodId);
        }

        foreach ($aCards as $iCard) {
            if ($oCard = Card::get($iCard)) {
                $oCard->delete();
            }
        }
    }

    /**
     * Удаляет поля типа (мульти-)справочник и (мульти-)коллекция из карточки.
     *
     * @param catalog\model\EntityRow $oCard
     *
     * @throws \yii\db\StaleObjectException
     */
    protected function deleteDictsAndCollectionsFromCard($oCard)
    {
        foreach ($oCard->getFields() as $field) {
            if (in_array($field->editor, [Editor::SELECT, Editor::MULTISELECT, Editor::COLLECTION, Editor::MULTICOLLECTION])) {
                if (in_array($field->editor, [Editor::COLLECTION, Editor::MULTICOLLECTION])) {
                    $oParam = ParamsAr::find()->where(['value' => $field->link_id . ':' . $field->name, 'name' => 'collectionField'])->one();

                    if ($oParam) {
                        $oTree = TreeSection::findOne($oParam->parent);
                        $oTree->delete();
                    }

                    $field->delete();
                } else {
                    $field->delete();
                }
            }
        }

        catalog\Entity::clearCache();
    }
}
