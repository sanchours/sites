<?php

namespace skewer\build\Tool\Copyright;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\base\site\Site;
use skewer\base\SysVar;
use skewer\build\Page;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentUser;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

class Api
{
    /**
     * Функция возвращает отсортированный набор видимых разделов(доступных по ссылке),
     * исключая шаблоны, библиотеки.
     *
     * @return array
     */
    public static function getAllSections()
    {
        $aSystemSections = Tree::getSubSections([\Yii::$app->sections->library(), \Yii::$app->sections->templates()], true, true);
        $aSystemSections = array_combine($aSystemSections, $aSystemSections);

        $aDenySection = Auth::getDenySectionByUserId(CurrentUser::getId());
        $aDenySection = array_combine($aDenySection, $aDenySection);

        $aExcludedSections = $aDenySection + $aSystemSections;

        $aSections = TreeSection::find()
            ->select(['id', 'title'])
            ->where("link LIKE ''")
            ->andWhere(['visible' => Visible::$aOpenByLink])
            ->andWhere(['NOT IN', 'id', $aExcludedSections])
            ->indexBy('id')
            ->orderBy('id')
            ->asArray()->all();

        return ArrayHelper::getColumn($aSections, static function ($aRow) {
            return '[' . $aRow['id'] . '] ' . $aRow['title'];
        });
    }

    /**
     * Вернёт массив разделов с отключенным модулем копирайта.
     *
     * @return array
     */
    public static function getSectionsWithDisabledCopyrightModule()
    {
        $sSectionList = SysVar::get(Page\Copyright\Module::getNameModule() . 'disabledSections', '');

        return StringHelper::explode($sSectionList, ',');
    }

    /**
     * Сохранение разделов с отключенным модулем копирайта.
     *
     * @param string $sSections - массив разделов на сохранение
     */
    public static function setSectionsWithDisabledCopyrightModule($sSections)
    {
        SysVar::set(Page\Copyright\Module::getNameModule() . 'disabledSections', $sSections);
    }

    /**
     * Вернет шаблонный текст с учетом языка.
     *
     * @param  string $sLanguage - язык
     * @param  bool $bDoParse - распарсить метки?
     *
     * @return bool|string
     */
    public static function getTemplatedText($sLanguage, $bDoParse)
    {
        $sText = \Yii::t('copyright', 'templateText', [], $sLanguage);

        if (!$sText) {
            return false;
        }

        if ($bDoParse) {
            $aLabels = [];
            $aAddressSite = \Yii::$app->getI18n()->getValues('copyright', 'addressSite_label');
            foreach ($aAddressSite as $sAddressSiteItem) {
                $aLabels[$sAddressSiteItem] = \yii\helpers\Html::a(\Yii::$app->request->getAbsoluteUrl(), \Yii::$app->request->getAbsoluteUrl());
            }

            $aSiteName = \Yii::$app->getI18n()->getValues('copyright', 'siteName_label');
            foreach ($aSiteName as $sSiteNameItem) {
                $aLabels[$sSiteNameItem] = Site::getSiteTitle();
            }

            foreach ($aLabels as $key => $value) {
                $sText = str_replace('[' . $key . ']', $value, $sText);
            }
        }

        $sText = str_replace(["\r", "\n"], '', $sText);

        return $sText;
    }

    public static function getActivityModule()
    {
        return SysVar::get('copyright.activity', false);
    }

    public static function setActivityModule($bActivity)
    {
        SysVar::set('copyright.activity', (int) $bActivity);
    }
}
