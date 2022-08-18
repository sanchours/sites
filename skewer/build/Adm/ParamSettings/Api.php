<?php

namespace skewer\build\Adm\ParamSettings;

use skewer\base\section\Parameters;
use skewer\base\section\params\Type;
use yii\helpers\ArrayHelper;

/**
 * Апи для работы со специализированными параметрами модулей.
 */
class Api
{
    /** Для всех разделов */
    const SECTION_ALL = 'all';
    /** Для корневого раздела (id=3) */
    const SECTION_ROOT = 'root';
    /** Для текущего языкового раздела */
    const SECTION_LANG = 'lang';
    /** Для раздела главной страницы текущего языка */
    const SECTION_MAIN = 'main';

    /** Слой с модулями, где искать редактируемые параметры */
    public static $sLayer = 'Page';

    /** Имя параметра конфигурации для текущего модуля */
    public static $sConfigName = 'param_settings';

    /**
     * Получить список объектов класса ParamSettings из уставновленных модулей.
     *
     * @return Prototype[]
     */
    public static function getModulesParamsObjects()
    {
        $aParamsObjects = [];

        foreach (\Yii::$app->register->getModuleList(self::$sLayer) as $sModule) {
            $sParamsClass = \Yii::$app->register->getModuleConfig($sModule, self::$sLayer)->getVal(self::$sConfigName);
            if ($sParamsClass) {
                $aParamsObjects[] = new $sParamsClass();
            }
        }

        return $aParamsObjects;
    }

    /**
     * Получить модули(а также их подсущности)
     * которые можно установить на страницу.
     *
     * @return array
     */
    public static function getInstallableModules()
    {
        $aModules = [];

        foreach (\Yii::$app->register->getModuleList(self::$sLayer) as $sModule) {
            if ($sParamsClass = \Yii::$app->register->getModuleConfig($sModule, self::$sLayer)->getVal(self::$sConfigName)) {
                /* @var Prototype $oParamsClass */
                if (!class_exists($sParamsClass)) {
                    continue;
                }

                $oParamsClass = new $sParamsClass();

                foreach (ArrayHelper::map($oParamsClass->getInstallationParam(), 'name', 'title') as $sName => $sTitle) {
                    $aModules[$sParamsClass . ':' . $sName] = $sTitle;
                }
            }
        }

        return $aModules;
    }

    /**
     * Получить набор параметров из уставновленных модулей.
     *
     * @return array Массив параметров модулей
     */
    public static function getModulesParams()
    {
        /** Параметры для редактирования */
        $aParams = [];

        // Сбор параметров для редактирования из установленных модулей
        foreach (self::getModulesParamsObjects() as $oParamsObject) {
            $aParams = array_merge($aParams, $oParamsObject->getList());
        }

        // Присвоить значение типа раздела по умолчанию. Это должны быть здесь, т. к. используется в \skewer\components\i18n\command\add_branch\ParamSettings()
        foreach ($aParams as &$aParam) {
            if (!isset($aParam['section'])) {
                $aParam['section'] = self::SECTION_LANG;
            }
        }

        return $aParams;
    }

    /**
     * Получить группы параметров из уставновленных модулей.
     *
     * @return array
     */
    public static function getModulesGroups()
    {
        $aGroups = [];
        $aGroupsSort = [];

        foreach (self::getModulesParamsObjects() as $oParamsObject) {
            if (isset($aGroupsSort[$oParamsObject::$iGroupSortIndex])) {
                $aGroupsSort[] = $oParamsObject::$aGroups;
            } else {
                $aGroupsSort[$oParamsObject::$iGroupSortIndex] = $oParamsObject::$aGroups;
            }
        }

        ksort($aGroupsSort);

        foreach (array_values($aGroupsSort) as $aGroupList) {
            $aGroups += $aGroupList;
        }

        return $aGroups;
    }

    /**
     * Получить набор параметров для редактирования.
     *
     * @param string $language
     * @return \skewer\base\section\models\ParamsAr[]
     */
    public static function getParameters($language)
    {
        /** Параметры для редактирования */
        $aParams = self::getModulesParams();

        /** Массив соответствий типов редакторов */
        $aParamTypes = array_flip(ArrayHelper::getColumn(Type::getParamTypes(), 'type'));

        /** @var \skewer\base\section\models\ParamsAr[] $aParamsList */
        $aParamsList = [];

        foreach ($aParams as &$aParam) {
            // Расставить id секций для параметров
            switch ($aParam['section']) {
                default:
                    if (is_numeric($aParam['section'])) {
                        $iSection = $aParam['section'];
                    } else {
                        $iSection = \Yii::$app->sections->languageRoot($language);
                    }
                    break;

                case self::SECTION_LANG:
                    $iSection = \Yii::$app->sections->languageRoot($language);
                    break;

                case self::SECTION_ALL:
                    $iSection = \Yii::$app->sections->tplNew();
                    break;

                case self::SECTION_ROOT:
                    $iSection = \Yii::$app->sections->root($language);
                    break;

                case self::SECTION_MAIN:
                    $iSection = \Yii::$app->sections->main($language);
                    break;
            }

            if (isset($aParam['options']) and is_array($aParam['options'])) {
                $aParam['show_val'] = self::optionsToStr($aParam['options']);
            }

            // Поиск/создание параметра
            $oParam = Parameters::getByName($iSection, $aParam['label'] ?? $aParam['group'], $aParam['name']);

            if (!$oParam) {
                $aNewRow = $aParam + [
                    'parent' => $iSection,
                    'value' => $aParam['default'] ?? '',
                    'access_level' => 0,
                ];
                $aNewRow['group'] = $aParam['label'] ?? $aParam['group'];

                $oParam = Parameters::createParam($aNewRow);
                $oParam->save();
            }
            // Выполнить обновление атрибутов. Актуально например если в БД не будет определёно у параметра title. или нужно переопределить группу
            $oParam->setAttributes($aParam);
            $oParam->settings = $aParam['settings'] ?? [];

            // Установить тип редактора параметру
            $oParam->access_level = (isset($aParam['editor']) and isset($aParamTypes[$aParam['editor']])) ? $aParamTypes[$aParam['editor']] : Type::paramString;
            $aParamsList[] = $oParam;
        }

        return $aParamsList;
    }

    /**
     * Преобразовать варианты значений для выпадающего списка.
     *
     * @param array $aOptions Варианты значений в формате массива
     *
     * @return string Варианты значений в формате строки
     */
    private static function optionsToStr(array $aOptions)
    {
        $sResult = '';

        foreach ($aOptions as $sKey => $sOption) {
            if ($sKey) {
                $sResult .= "{$sKey}:{$sOption}\n";
            }
        }

        return rtrim($sResult);
    }
}
