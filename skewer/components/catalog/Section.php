<?php

namespace skewer\components\catalog;

use skewer\base\orm\Query;
use skewer\base\section\models\ParamsAr as ParamModel;
use skewer\base\section\Page;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\components\auth\Auth;
use skewer\components\catalog\model\SectionTable;
use skewer\components\i18n\Languages;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с каталожными разделами
 * Class Section.
 */
class Section
{
    /**
     * @var array
     */
    private static $cache = [];

    /**
     * @param mixed $key
     *
     * @return string
     */
    private static function getCache($key)
    {
        return (isset(self::$cache[$key])) ? self::$cache[$key] : '';
    }

    /**
     * @param array $cache
     * @param mixed $key
     * @param mixed $value
     */
    private static function setCache($key, $value = '')
    {
        self::$cache[$key] = $value;
    }

    /**
     * Список каталожных разделов.
     *
     * @param bool $bOnlyVisible Только видимые разделы?
     *
     * @return array
     */
    public static function getList($bOnlyVisible = false)
    {
        $list = Parameters::getListByModule('CatalogViewer', 'content');

        $oQuery = Query::SelectFrom('tree_section')
            ->where('id', $list)
            ->where('parent<>?', \Yii::$app->sections->templates())
            ->asArray();

        if ($bOnlyVisible) {
            $oQuery
                ->where('visible IN ?', Visible::$aOpenByLink)
                ->where('link = ?', '');
        }

        $aList = [];
        $aSearchList = self::getSearchList();
        $aCollectionList = self::getCollectionList();

        while ($aItem = $oQuery->each()) {
            if ($aItem['id'] && $aItem['title'] && !isset($aSearchList[$aItem['id']]) && !isset($aCollectionList[$aItem['id']])) {
                $aList[$aItem['id']] = $aItem['title'];
            }
        }

        return $aList;
    }

    public static function getListWithStructure()
    {
        $aSections = [];

        $iPolicyId = Auth::getPolicyId('public');

        if ($aLanguages = Languages::getAllActiveNames()) {
            if ($aLanguages) {
                foreach ($aLanguages as $sLang) {
                    if (count($aLanguages) > 1) {
                        $aSections[\Yii::$app->sections->getValue(Page::LANG_ROOT, $sLang)] = Tree::getSectionTitle(\Yii::$app->sections->getValue(Page::LANG_ROOT, $sLang), true);
                    }

                    $aSectionsFromTopMenu = Tree::getSectionList(\Yii::$app->sections->getValue('topMenu', $sLang), $iPolicyId);
                    $aSectionsFromLeftMenu = Tree::getSectionList(\Yii::$app->sections->getValue('leftMenu', $sLang), $iPolicyId);

                    $aSections = array_replace(
                        $aSections,
                        ArrayHelper::map($aSectionsFromTopMenu, 'id', 'title'),
                        ArrayHelper::map($aSectionsFromLeftMenu, 'id', 'title')
                    );
                }
            }
        }

        return $aSections;
    }

    /**
     * Список поисковых каталожных разделов.
     *
     * @return array
     */
    public static function getSearchList()
    {
        $oQuery = Query::SelectFrom('tree_section')
            ->fields('tree_section.*')
            ->join('inner', 'parameters', 'parameters', 'tree_section.id=parameters.parent')
            ->on('name', 'searchCard')
            ->asArray();

        $aList = [];

        while ($aItem = $oQuery->each()) {
            if ($aItem['id'] && $aItem['title']) {
                $aList[$aItem['id']] = $aItem['title'];
            }
        }

        return $aList;
    }

    /**
     * Список каталожных разделов с товарами коллекции.
     *
     * @return array
     */
    public static function getCollectionList()
    {
        $oQuery = Query::SelectFrom('tree_section')
            ->fields('tree_section.*')
            ->join('inner', 'parameters', 'parameters', 'tree_section.id=parameters.parent')
            ->on('name', 'collectionField')
            ->asArray();

        $aList = [];

        while ($aItem = $oQuery->each()) {
            if ($aItem['id'] && $aItem['title']) {
                $aList[$aItem['id']] = $aItem['title'];
            }
        }

        return $aList;
    }

    /**
     * Список разделов вывода для товарной позиции.
     *
     * @param int $iGoodId Ид товарной позиции
     * @param int $iBaseCard ID базовой карточки
     *
     * @return array|bool
     */
    public static function getList4Goods($iGoodId, $iBaseCard)
    {
        return model\SectionTable::get4Goods($iGoodId, $iBaseCard);
    }

    /**
     * Привязка товара к разделу.
     *
     * @param int $iSectionId Ид раздела
     * @param int $iGoodId Ид товарной позиции
     * @param int $iBaseCard Ид базовой карточки
     * @param int $iExtCard Ид карточки
     *
     * @return bool
     */
    public static function addGoods($iSectionId, $iGoodId, $iBaseCard, $iExtCard)
    {
        return model\SectionTable::link($iSectionId, $iGoodId, $iBaseCard, $iExtCard);
    }

    /**
     * Отвязка товара от раздела.
     *
     * @param int $iSectionId Ид раздела
     * @param int $iGoodId Ид товарной позиции
     * @param int $iBaseCard Ид базовой карточки
     *
     * @return bool
     */
    public static function removeGoods($iSectionId, $iGoodId, $iBaseCard)
    {
        return model\SectionTable::unlink($iSectionId, $iGoodId, $iBaseCard);
    }

    /**
     * Установка основного раздела для товара.
     *
     * @param $iGoodsId
     * @param $iSectionId
     *
     * @return bool
     */
    public static function setMain4Goods($iGoodsId, $iSectionId)
    {
        return model\GoodsTable::setMainSection($iGoodsId, $iSectionId);
    }

    /**
     * Получение/установка основного раздела для товара.
     *
     * @param $iGoodsId
     * @param $iBaseCard
     *
     * @throws \Exception
     *
     * @return bool|mixed
     */
    public static function getMain4Goods($iGoodsId, $iBaseCard)
    {
        $iSectionId = model\GoodsTable::getMainSection($iGoodsId);

        if (!$iSectionId) {
            if (count($aSectionList = self::getList4Goods($iGoodsId, $iBaseCard))) {
                foreach ($aSectionList as $iSectionId) {
                    // Только если раздел не является ссылкой и видимый
                    if ($aSection = Tree::getCachedSection($iSectionId) and
                         !$aSection['link'] and in_array($aSection['visible'], Visible::$aOpenByLink)) {
                        self::setMain4Goods($iGoodsId, $iSectionId);

                        return $iSectionId;
                    }
                }
            }

            return false;
        }

        return $iSectionId;
    }

    /**
     * Набор карточек для раздела.
     *
     * @param array|int $section
     *
     * @return int[]
     */
    public static function getCardList($section)
    {
        return model\SectionTable::cardList($section);
    }

    /**
     * Актуализация связей товара с разделами.
     *
     * @param int $iGoodsId Ид товарной позиции
     * @param int $iBaseCard Ид базовой карточки товара
     * @param int $iExtCard Ид карточки товара
     * @param int[] $aSectionList Список инедтификаторов разделов
     */
    public static function save4Goods($iGoodsId, $iBaseCard, $iExtCard, $aSectionList)
    {
        $sections = [];
        foreach ($aSectionList as $iSection) {
            if ((int) $iSection) {
                $sections[] = (int) $iSection;
            }
        }

        $aCurSectionList = Section::getList4Goods($iGoodsId, $iBaseCard);

        foreach ($sections as $iSection) {
            if (!$aCurSectionList || !in_array($iSection, $aCurSectionList)) {
                Section::addGoods($iSection, $iGoodsId, $iBaseCard, $iExtCard);
            }
        }

        if ($aCurSectionList) {
            foreach ($aCurSectionList as $iSection) {
                if (!in_array($iSection, $sections)) {
                    Section::removeGoods($iSection, $iGoodsId, $iBaseCard);
                }
            }
        }
        //надо пересобрать кеш
        SectionTable::setUpdateCache(true);
        Section::getList4Goods($iGoodsId, $iBaseCard);
    }

    /**
     * Очистка основного раздела.
     *
     * @param int $iSectionId Ид раздела
     *
     * @return bool
     */
    public static function clearSection($iSectionId)
    {
        model\GoodsTable::removeSection($iSectionId);

        return true;
    }

    /**
     * Получение карточки для нового товара в разеделе $section.
     *
     * @param int $section Идентификатор раздела
     *
     * @return string
     */
    public static function getDefCard($section)
    {
        if (!isset(self::$cache[$section])) {
            $sVal = Parameters::getValByName($section, 'content', 'defCard');
            self::setCache($section, $sVal);
        }

        return self::getCache($section);
    }

    /**
     * Сохранение карточки для новых товаров в разделе $section.
     *
     * @param int $section
     * @param $card
     *
     * @return bool
     */
    public static function setDefCard($section, $card)
    {
        $oParam = Parameters::getByName($section, 'content', 'defCard');

        if (!$oParam) {
            $oParam = Parameters::createParam([
                'parent' => $section,
                'group' => 'content',
                'name' => 'defCard',
            ]);
        }

        $oParam->value = $card;

        return $oParam->save();
    }

    /**
     * Сохранение карточки для новых товаров в разделе $section.
     *
     * @param int $section
     * @param $name
     * @param $value
     *
     * @return bool
     */
    public static function setParam($section, $name, $value)
    {
        $oParam = Parameters::getByName($section, 'content', $name);

        if (!$oParam) {
            $oParam = Parameters::createParam([
                'parent' => $section,
                'group' => 'content',
                'name' => $name,
            ]);
        }

        $oParam->value = $value;

        return $oParam->save();
    }

    /**
     * Получить раздел в котором находится коллекция.
     *
     * @param int $card Идентификатор сущности коллекции
     *
     * @return int
     */
    public static function get4Collection($card)
    {
        $out = 0;

        // ищем разделы с коллекциями
        $query = ParamModel::find()->where(['name' => 'collectionField']);
        foreach ($query->each() as $param) {
            list($curCard) = explode(':', $param->value);

            if ($card == $curCard) {
                $out = $param->parent;
            }
        }

        return $out;
    }

    /**
     * Получить раздел с коллекцией связанной с полем $field.
     *
     * @param string $field Название поля карточки
     *
     * @return int
     */
    public static function get4CollectionField($field)
    {
        $out = 0;

        // ищем разделы с коллекциями
        $query = ParamModel::find()->where(['name' => 'collectionField']);
        foreach ($query->each() as $param) {
            list(, $curField) = explode(':', $param->value);

            if ($field == $curField) {
                $out = $param->parent;
            }
        }

        return $out;
    }
}
