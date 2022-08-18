<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 07.09.2017
 * Time: 11:55.
 */

namespace skewer\base\section;

use skewer\base\section\models\TreeSection;
use skewer\build\Tool\LeftList\Group;
use yii\helpers\ArrayHelper;

class Template extends Tree
{
    /** @var array Кеш шаблонов */
    private static $aTemplateCache;

    /**
     * Получить кеш шаблонов.
     *
     * @return array
     */
    private static function getTemplateCache()
    {
        return self::$aTemplateCache;
    }

    /**
     * Установить кеш шаблонов.
     *
     * @param $aTemplates array
     */
    private static function setTemplateCache($aTemplates)
    {
        self::$aTemplateCache = $aTemplates;
    }

    /**
     * Очистить кеш шаблонов.
     */
    public static function clearTemplateCache()
    {
        self::$aTemplateCache = [];
    }

    /**
     * Получить id шаблона "Новая страница".
     *
     * @return int | array
     */
    public static function getNewTemplate()
    {
        return \Yii::$app->sections->tplNew();
    }

    /**
     * Получить id шаблона "Новости".
     *
     * @param bool $bReturnFirst - Если =true, то вернёт первый попавщийся шаблон, иначе список шаблонов,
     *                              которые соответствуют модулю "Новости"
     *
     * @return int | array
     */
    public static function getNewsTemplate($bReturnFirst = true)
    {
        return self::getTemplateIdForModule('News', $bReturnFirst);
    }

    /**
     * Получить id шаблона "Статьи".
     *
     * @param bool $bReturnFirst - Если =true, то вернёт первый попавщийся шаблон, иначе список шаблонов,
     *                              которые соответствуют модулю "Статьи"
     *
     * @return int | array
     */
    public static function getArticlesTemplate($bReturnFirst = true)
    {
        return self::getTemplateIdForModule('Articles', $bReturnFirst);
    }

    /**
     * Получить id шаблона "Вопрос-ответ".
     *
     * @param bool $bReturnFirst Если =true, то вернёт первый попавщийся шаблон, иначе список шаблонов,
     *                              которые соответствуют модулю "FAQ"
     *
     * @return int | array
     */
    public static function getFAQTemplate($bReturnFirst = true)
    {
        return self::getTemplateIdForModule('FAQ', $bReturnFirst);
    }

    /**
     * Получить id шаблона "Фотоальбом".
     *
     * @param bool $bReturnFirst - Если =true, то вернёт первый попавщийся шаблон, иначе список шаблонов,
     *                              которые соответствуют модулю "Gallery"
     *
     * @return int | array
     */
    public static function getGalleryTemplate($bReturnFirst = true)
    {
        return self::getTemplateIdForModule('Gallery', $bReturnFirst);
    }

    /**
     * Получить id шаблона "Каталог".
     *
     * @param bool $bReturnFirst - Если =true, то вернёт первый попавщийся шаблон, иначе список шаблонов,
     *                              которые соответствуют модулю "CatalogViewer"
     *
     * @return int | array
     */
    public static function getCatalogTemplate($bReturnFirst = true)
    {
        return self::getTemplateIdForModule('CatalogViewer', $bReturnFirst);
    }

    /**
     * Получить id шаблона "Отзывы".
     *
     * @param bool $bReturnFirst - Если =true, то вернёт первый попавщийся шаблон, иначе список шаблонов,
     *                              которые соответствуют модулю "GuestBook"
     *
     * @return int | array
     */
    public static function getReviewsTemplate($bReturnFirst = true)
    {
        return self::getTemplateIdForModule('GuestBook', $bReturnFirst);
    }

    /**
     * По имени модуля находит шаблон для него.
     *
     * @param string $sModuleName - имя модуля
     * @param bool $bReturnFirst - Если =true, то вернёт первый попавщийся шаблон, иначе список шаблонов,
     *                              которые соответствуют модулю $sModuleName
     *
     * @return array|int id раздела или 0
     */
    public static function getTemplateIdForModule($sModuleName, $bReturnFirst = true)
    {
        $sModuleName = mb_strtolower($sModuleName);

        $bRebuildCache = !isset(self::getTemplateCache()[$sModuleName]);

        if ($bRebuildCache) {
            // запросить список id шаблонных разделов
            $aSectionList = self::getSectionByParent(\Yii::$app->sections->templates());
            $aIds = ArrayHelper::getColumn($aSectionList, 'id');

            $aTemplates = [];

            foreach ($aIds as $id) {
                // отдать первый попавшийся
                $oParam = Parameters::getByName($id, Group::CONTENT, 'object', true);
                if ($oParam and $oParam->value) {
                    $sGroup = mb_strtolower($oParam->value);
                    $aTemplates[$sGroup][] = $id;
                }
            }

            self::setTemplateCache($aTemplates);
        }

        $aTemplatesOfModule = ArrayHelper::getValue(self::getTemplateCache(), $sModuleName);

        if (!empty($aTemplatesOfModule)) {
            if ($bReturnFirst) {
                return reset($aTemplatesOfModule);
            }

            return $aTemplatesOfModule;
        }
        // если не нашли, то 0
        return 0;
    }

    /**
     * Выборка разделов по их шаблону.
     *
     * @param $iTplId
     * @param mixed $sSort
     *
     * @return array
     */
    public static function getSectionsByTplId($iTplId, $sSort = '')
    {
        $command = \Yii::$app
            ->db
            ->createCommand("SELECT `tree_section`.* FROM `tree_section`
                              INNER JOIN `parameters` ON `tree_section`.`id`=`parameters`.`parent`
                              WHERE `parameters`.`name`='template' AND `parameters`.`value`='{$iTplId}' " . $sSort);
        $rows = $command->queryAll();

        return $rows;
    }

    /**
     * Вернет полную цепочку разделов, унаследованных от шаблона $mTpl.
     *
     * @param $mTpl array | int - шаблон
     * @param $aSections - массив разделов(для рекурсии)
     *
     * @return array
     */
    public static function getSubSectionsByTemplate($mTpl, array &$aSections = [])
    {
        $aChildSections = Parameters::getList()
            ->fields(['parent'])
            ->group(Parameters::settings)
            ->name(Parameters::template)
            ->value($mTpl)
            ->asArray()->get();

        if (!$aChildSections = ArrayHelper::getColumn($aChildSections, 'parent')) {
            return $aSections;
        }

        return ArrayHelper::merge($aSections, self::getSubSectionsByTemplate($aChildSections, $aChildSections));
    }

    /**
     * Список шаблонов.
     *
     * @param bool $bOnlyVisible
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getTemplateList($bOnlyVisible = true)
    {
        $oQuery = TreeSection::find()
            ->where(['parent' => \Yii::$app->sections->templates()]);

        if ($bOnlyVisible) {
            $oQuery
                ->andWhere(['visible' => Visible::VISIBLE]);
        }

        $aSections = $oQuery
            ->asArray()
            ->all();

        return $aSections;
    }
}
