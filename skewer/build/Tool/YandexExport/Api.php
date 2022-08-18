<?php

namespace skewer\build\Tool\YandexExport;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\components\catalog\Section;
use skewer\components\i18n\Languages;
use yii\helpers\ArrayHelper;

class Api
{
    /** @var array  */
    private static $cacheSection = null;

    /**
     * Проставляет товарам выбранного раздела значения.
     *
     * @param $iSectionId
     * @param $aData
     *
     * @throws \yii\db\Exception
     *
     * @return int
     */
    public static function setDataForSectionGoods($iSectionId, $aData)
    {
        if (isset($aData['isCatalogSection'])) {
            unset($aData['isCatalogSection']);
        }

        $aValues = [];
        foreach ($aData as $key => $item) {
            $aValues[] = '`' . $key . '`' . '=' . (int) $item;
        }

        $sValues = implode(',', $aValues);

        $sQuery = 'UPDATE co_base_card
                    set
                    {{data}}
                    WHERE id IN
                    (
                      SELECT goods_id as id
                      FROM cl_section
                      WHERE section_id={{section_id}}
                    )';

        $sQuery = str_replace('{{section_id}}', $iSectionId, $sQuery);
        $sQuery = str_replace('{{data}}', $sValues, $sQuery);

        return \Yii::$app->db->createCommand($sQuery)->execute();
    }

    /**
     * Соберает все каталожные разделы, экспортируемые в Яндекс и их родителей.
     *
     * @return array
     */
    public static function getSections()
    {
        $aSectionsAll = Tree::getCachedSection(0, true);

        $aSectionsOut = [];
        foreach ($aSectionsAll as $iSectionId => &$paSection) {
            /** Это каталожной раздел с заданной карточкой? */
            $bIsCatalog = (Parameters::getValByName($iSectionId, 'content', Parameters::objectAdm, true) === 'Catalog');
            $bIsCatalog = ($bIsCatalog and (bool) Section::getDefCard($iSectionId));

            if ($bIsCatalog and self::checkVisibleSection($iSectionId)) {
                $aSectionsOut[$iSectionId] = [
                    'id' => $iSectionId,
                    'title' => $paSection['title'],
                    'isCatalogSection' => $bIsCatalog,
                    'parent' => self::getParentSections($paSection['parent'], $aSectionsOut, $aSectionsAll),
                ];
            }
        }

        return $aSectionsOut;
    }

    /**
     * Проверка видимости раздела на сайте.
     *
     * @param int $iSectionId Id раздела
     *
     * @return bool
     */
    public static function checkVisibleSection($iSectionId)
    {
        /** Массив id родительских разделов всего дерева сайта для всех языков */
        static $aStopSections = [];
        if (!$aStopSections) {
            foreach (Languages::getAllActiveNames() as $sLang) {
                $aStopSections[\Yii::$app->sections->topMenu($sLang)] = 1;
                $aStopSections[\Yii::$app->sections->leftMenu($sLang)] = 1;
            }
        }

        if (isset($aStopSections[$iSectionId])) {
            return true;
        }

        $aSection = $iSectionId
            ? ArrayHelper::getValue(self::getSectionsForExport(), $iSectionId)
            : null;
        if (!$aSection) {
            return false;
        }

        if ($aSection['visible'] == Visible::HIDDEN_NO_INDEX) {
            return false;
        }

        $iParentId = $aSection['parent'];

        if ($aSection['visible'] == Visible::VISIBLE) {
            // Если родитель ссылается на другой раздел
            if ($iRedirectId = (int) trim($aSection['link'], '[]')) {
                $aParentSections = Tree::getSectionParents($iRedirectId);
                if (array_search($iSectionId, $aParentSections) === false) {
                    $iParentId = $iRedirectId;
                } else {
                    return true;
                }
            }
        }

        return self::checkVisibleSection($iParentId);
    }

    /**
     * Возвращает массив id видимых и существующих разделов.
     *
     * @return array
     */
    public static function getSectionsForExport()
    {
        if (is_null(self::$cacheSection)) {
            self::$cacheSection = TreeSection::find()
                ->where(['!=', 'visible', Visible::HIDDEN_NO_INDEX])
                ->indexBy('id')
                ->asArray()
                ->all();
        }
        return self::$cacheSection;
    }

    /**
     * Добавить разделы-родителей.
     *
     * @param int $iSectionId Id текущего родительского раздела
     * @param array $paSectionsOut Указатель на выходной массив разделов
     * @param array $paSectionsAll Указатель на все видимые разделы сайта
     *
     * @return bool|int Вернёт id обработанного родительского раздела или false
     */
    public static function getParentSections($iSectionId, &$paSectionsOut, &$paSectionsAll)
    {
        // Если родитель уже есть, то выйти
        if (isset($paSectionsOut[$iSectionId])) {
            return $iSectionId;
        }

        /** Массив id родительских разделов всего дерева сайта для всех языков */
        static $aStopSections = [];
        if (!$aStopSections) {
            foreach (Languages::getAllActiveNames() as $sLang) {
                $aStopSections[\Yii::$app->sections->root($sLang)] = 1;
                $aStopSections[\Yii::$app->sections->topMenu($sLang)] = 1;
                $aStopSections[\Yii::$app->sections->leftMenu($sLang)] = 1;
                $aStopSections[\Yii::$app->sections->serviceMenu($sLang)] = 1;
                $aStopSections[\Yii::$app->sections->tools($sLang)] = 1;
            }
        }

        if (isset($aStopSections[$iSectionId])) {
            return false;
        }

        if (!isset($paSectionsAll[$iSectionId])) {
            $aSection = ArrayHelper::getValue(self::getSectionsForExport(), $iSectionId);
            if (!$aSection) {
                return false;
            }

            // Если родитель скрыт из пути, то обработать родителя уровнем выше
            if ($aSection['visible'] == Visible::HIDDEN_FROM_PATH) {
                return self::getParentSections($aSection['parent'], $paSectionsOut, $paSectionsAll);
            }

            if ($aSection['visible'] != Visible::VISIBLE) {
                return false;
            }

            // Если родитель ссылается на другой раздел
            if ($iRedirectId = (int) trim($aSection['link'], '[]')) {
                $aParentSections = Tree::getSectionParents($iRedirectId);
                if (array_search($iSectionId, $aParentSections) === false) {
                    return self::getParentSections($iRedirectId, $paSectionsOut, $paSectionsAll);
                }
            }

            return false;
        }
        $aSection = $paSectionsAll[$iSectionId];

        // Добавить родителя
        $bIsCatalog = (Parameters::getValByName($iSectionId, 'content', Parameters::objectAdm, true) === 'Catalog');
        $paSectionsOut[$iSectionId] = [
            'id' => $iSectionId,
            'title' => $aSection['title'],
            'isCatalogSection' => $bIsCatalog,
            'parent' => self::getParentSections($aSection['parent'], $paSectionsOut, $paSectionsAll),
        ];

        return $iSectionId;
    }
} //class
