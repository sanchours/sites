<?php

namespace skewer\base\section;

use skewer\base\section\params\Type;
use yii\helpers\ArrayHelper;

/**
 * Класс для хранения параметров текущего раздела
 * Class Page.
 */
class Page
{
    const LANG_ROOT = 'lang_root';

    /** @var array Параметры */
    private static $params = [];

    /** @var array Параметры основного раздела языка */
    private static $langParentParams = null;

    /** @var bool|int */
    private static $iParent = false;

    /**
     * Установка параметров по разделу.
     *
     * @param $iParent
     */
    public static function init($iParent)
    {
        self::$iParent = $iParent;
        $aParams = Parameters::getList($iParent)->rec()->asArray()->groups()->get();
        self::$params = [];

        if ($aParams) {
            foreach ($aParams as $sGroup => $aParamList) {
                foreach ($aParamList as &$aParam) {
                    if ($aParam['access_level'] == Type::paramServiceSection) {
                        $aParam['value'] = \Yii::$app->sections->getValue($aParam['value']);
                    }

                    if ($aParam['access_level'] == Type::paramLanguage) {
                        $aLangParams = self::getLangParams($aParam['group'], $aParam['name']);
                        if ($aLangParams) {
                            $aParam['value'] = $aLangParams['value'];
                            $aParam['show_val'] = $aLangParams['show_val'];
                        }
                    }
                }
                /* @var array $aParamList */
                self::$params[$sGroup] = ArrayHelper::index($aParamList, 'name');
            }
        }
    }

    /**
     * Получение языкового параметра.
     *
     * @param $sGroup
     * @param $sName
     *
     * @return bool
     */
    private static function getLangParams($sGroup, $sName)
    {
        if (self::$langParentParams === null) {
            $iLangRoot = \Yii::$app->sections->languageRoot();
            if (!$iLangRoot) {
                $iLangRoot = \Yii::$app->sections->root();
            }

            /* Будем запрашивать все. Если это и будет использоваться, то для нескольких параметров */
            self::$langParentParams = Parameters::getList($iLangRoot)
                ->asArray()
                ->groups()
                ->get();

            if (self::$langParentParams) {
                foreach (self::$langParentParams as &$aGroups) {
                    /** @var array $aGroups */
                    $aGroups = ArrayHelper::index($aGroups, 'name');
                }
            }
        }

        return (isset(self::$langParentParams[$sGroup][$sName])) ? self::$langParentParams[$sGroup][$sName] : false;
    }

    /**
     * Список групп параметров.
     *
     * @return array
     */
    public static function getGroups()
    {
        return array_keys(self::$params);
    }

    /**
     * Все параметры группы.
     *
     * @param $sGroup
     *
     * @return array
     */
    public static function getByGroup($sGroup)
    {
        return (isset(self::$params[$sGroup])) ? self::$params[$sGroup] : [];
    }

    /**
     * Возвращает значение параметра по группе и имени.
     *
     * @param $sGroup
     * @param $sName
     *
     * @return string
     */
    public static function getVal($sGroup, $sName)
    {
        return (isset(self::$params[$sGroup][$sName])) ? self::$params[$sGroup][$sName]['value'] : false;
    }

    /**
     * Возвращает расширеное значение параметра по группе и имени.
     *
     * @param $sGroup
     * @param $sName
     *
     * @return string
     */
    public static function getShowVal($sGroup, $sName)
    {
        return (isset(self::$params[$sGroup][$sName])) ? self::$params[$sGroup][$sName]['show_val'] : false;
    }

    /**
     * Возвращает все параметры раздела, сгруппированные по группам
     *
     * @return array
     */
    public static function getAllParams()
    {
        return self::$params;
    }
}
