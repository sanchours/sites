<?php

namespace skewer\components\catalog;

use skewer\base\ft;
use skewer\base\section\Tree;
use skewer\base\site\Site;
use skewer\build\Catalog\Goods\SeoGood;
use skewer\build\Catalog\Goods\SeoGoodModifications;
use skewer\components\catalog\field\Prototype;
use skewer\components\regions\RegionHelper;
use yii\helpers\ArrayHelper;

/**
 * Парсер. Переводит товар из формата ActiveRecord в ассоциативный массив для вывода
 * Class Parser.
 */
class Parser
{
    /** @var field\Prototype[] набор полей для формирования вывода */
    private $fields = [];

    /**
     * Инициализация парсера.
     *
     * @param ft\model\Field[] $fields набор полей для вывода
     * @param array $attr набор атрибутов, ограничивающий набор полей
     *
     * @return Parser
     */
    public static function get($fields, $attr = [])
    {
        $obj = new self();

        foreach ($fields as $ftField) {
            $fl = true;

            foreach ($attr as $name) {
                if (!$ftField->getAttr($name)) {
                    $fl = false;
                }
            }

            if (!$fl) {
                continue;
            }

            $oCatalogField = field\Prototype::init($ftField);
            $oCatalogField->loadData();
            $obj->fields[] = $oCatalogField;
        }

        return $obj;
    }

    /**
     * Парсим кортеж данных как каталожную позицию.
     *
     * @param array $aGoodParam значения служебных полей каталожной позиции из таблицы c_goods
     * @param array $aGoodData данные карточек каталожной позиции
     * @param bool $onlyHeader
     * @param mixed $aGoodParams
     *
     * @return array
     */
    public function parseGood($aGoodParams, $aGoodData, $onlyHeader = false)
    {
        // заполняем заголовок - товарные данные
        $out = [
            'id' => ArrayHelper::getValue($aGoodData, 'id', 0),
            'title' => $sTitle = ArrayHelper::getValue($aGoodData, 'title', ''),
            'alias' => ArrayHelper::getValue($aGoodData, 'alias', ''),
            'active' => (bool) ArrayHelper::getValue($aGoodData, 'active', false),
            'card' => ArrayHelper::getValue($aGoodParams, 'ext_card_name', ''),
            'base_card_id' => (int) ArrayHelper::getValue($aGoodParams, 'base_card_id', 0),
            'main_obj_id' => ArrayHelper::getValue($aGoodParams, 'parent', ''),
            'main_section' => ArrayHelper::getValue($aGoodParams, 'section', 0),
            'last_modified_date' => ArrayHelper::getValue($aGoodParams, '__upd_date', ''),
        ];

        $out['url'] = self::buildUrl($out['main_section'], $out['id'], $out['alias']);

        if (RegionHelper::isInstallModuleRegion()) {
            $out['canonical_url'] = RegionHelper::getCanonical($out['url']);
        } else {
            $out['canonical_url'] = Site::httpDomain() . $out['url'];
        }

        // если нужен только заголовое, то дальше не обрабатываем поля
        if ($onlyHeader) {
            return $out;
        }

        // список доступных полей для товара
        $out['fields'] = [];
        foreach ($this->fields as $field) {
            $value = ArrayHelper::getValue($aGoodData, $field->getName(), '');
            $out['fields'][$field->getName()] = $field->parse($value, $out['id'], ['title' => $sTitle]);
        }

        foreach ($this->fields as $field) {
            $aFieldData = $field->afterParseGood($out, $out['fields'][$field->getName()]);
            if ($aFieldData !== null) {
                $out['fields'][$field->getName()] = $aFieldData;
            }
        }

        // список полей по группам
        $out['groups'] = [];

        return $out;
    }

    /**
     * Парсим кортеж данных как сущность.
     *
     * @param array $data данные полей сущности
     *
     * @return array
     */
    public function object($data)
    {
        $out = [
            'id' => ArrayHelper::getValue($data, 'id', 0),
            'title' => ArrayHelper::getValue($data, 'title', ''),
            'alias' => ArrayHelper::getValue($data, 'alias', ''),
            'card' => ArrayHelper::getValue($data, 'card', ''),
            'active' => (bool) ArrayHelper::getValue($data, 'active', false),
            'last_modified_date' => ArrayHelper::getValue($data, 'last_modified_date', ''),
        ];

        $out['fields'] = [];

        foreach ($this->fields as $field) {
            $out['fields'][$field->getName()] = $field->parse($data[$field->getName()], $out['id']);
        }

        return $out;
    }

    /**
     * Формирование относительного url для каталожной позиции.
     *
     * @param int $iMainSection Id секции
     * @param int $iGoods Id позиции
     * @param string $sGoodsAlias псевдоним позиции
     *
     * @return string
     */
    public static function buildUrl($iMainSection, $iGoods = 0, $sGoodsAlias = '')
    {
        $sSectionPath = Tree::getSectionAliasPath($iMainSection, true, false, true);

        if (!$sSectionPath or (!$iGoods and !$sGoodsAlias)) {
            return '';
        }

        return ($sGoodsAlias) ?
            "{$sSectionPath}{$sGoodsAlias}/" :
            "{$sSectionPath}?item={$iGoods}/";
    }

    /**
     * Вернет массив расспарсенных полей карточки.
     *
     * @param $sCard - карточка
     * @param $iGoodId - id товара
     * @param array $aData - данные для подстановки
     *
     * @return array
     */
    public static function parseFieldsByCard($sCard, $iGoodId, $aData = [])
    {
        $aOut = [];
        $aFields = GoodsRow::create($sCard)->getFields();

        foreach ($aFields as $oField) {
            if (isset($aData[$oField->getName()])) {
                $oParserField = Prototype::init($oField);
                $oParserField->loadData();
                $aOut[$oField->getName()] = $oParserField->parse($aData[$oField->getName()], $iGoodId);
            }
        }

        return $aOut;
    }

    /**
     * Добавляет seo - данные товара в массив.
     *
     * @param $aGoods - товары
     * @param $iSectionId - id текущего раздела
     *
     * @throws \Exception
     */
    public function addSeoDataInGoods(&$aGoods, $iSectionId)
    {
        $aCards = ArrayHelper::getColumn($aGoods, 'card');

        $aCards = array_count_values($aCards);

        if (count($aCards) > 1) {
            $sCard = Card::DEF_BASE_CARD;
        } else {
            reset($aCards);
            $sCard = key($aCards);
        }

        //Инициализируем seo - компоненты
        $oSeoComponent4Modifications = new SeoGoodModifications();
        $oSeoComponent4Modifications->setSectionId($iSectionId);

        $oSeoComponent4BaseGoods = new SeoGood();
        $oSeoComponent4BaseGoods->setExtraAlias($sCard);
        $oSeoComponent4BaseGoods->setSectionId($iSectionId);

        foreach ($aGoods as &$aGood) {
            //Выбираем нужные seo - компонент
            if ($aGood['id'] != $aGood['main_obj_id']) {
                $oCurrentSeoComponent = $oSeoComponent4Modifications;
            } else {
                $oCurrentSeoComponent = $oSeoComponent4BaseGoods;
            }

            //Инициализируем seo-компонент данными текущего товара
            $oCurrentSeoComponent->setEntityId($aGood['id']);
            $oCurrentSeoComponent->setDataEntity($aGood);

            foreach ($this->fields as $oField) {
                $aFieldData = $aGood['fields'][$oField->getName()];
                $oField->setSeo($oCurrentSeoComponent, $aFieldData, $iSectionId);
                $aGood['fields'][$oField->getName()] = $aFieldData;
            }
        }
    }
}
