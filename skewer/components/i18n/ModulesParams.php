<?php

namespace skewer\components\i18n;

use skewer\components\i18n\models\Params;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с параметрами модулей на разных языках
 * Class ModulesParams.
 */
class ModulesParams
{
    /**
     * Получение записи параметра по модулю и имени и текущему языку.
     *
     * @param $sModule
     * @param $sName
     * @param string $sLanguage Если не указан, подставляется текущий
     *
     * @return null|Params
     */
    public static function getParamByName($sModule, $sName, $sLanguage = '')
    {
        return Params::findOne(['module' => $sModule, 'name' => $sName, 'language' => $sLanguage ? $sLanguage : \Yii::$app->language]);
    }

    /**
     * Получение значения параметра по модулю и имени и текущему языку.
     *
     * @param $sModule
     * @param $sName
     * @param string $sLanguage Если не указан, подставляется текущий
     *
     * @return false|string
     */
    public static function getByName($sModule, $sName, $sLanguage = '')
    {
        /** @var Params $aParam */
        $aParam = Params::findOne(['module' => $sModule, 'name' => $sName, 'language' => $sLanguage ? $sLanguage : \Yii::$app->language]);

        if ($aParam) {
            return $aParam->value;
        }

        return false;
    }

    /**
     * Получение параметров модуля.
     *
     * @param string $sModule
     * @param string $sLanguage Если не указан, подставляется текущий
     *
     * @return array ['name' => 'value']
     */
    public static function getByModule($sModule, $sLanguage = '')
    {
        return ArrayHelper::map(Params::findAll(['module' => $sModule, 'language' => $sLanguage ? $sLanguage : \Yii::$app->language]), 'name', 'value');
    }

    /**
     * Сохраняет параметр
     *
     * @param $sModule
     * @param $sName
     * @param $sLanguage
     * @param $sValue
     *
     * @return bool
     */
    public static function setParams($sModule, $sName, $sLanguage, $sValue)
    {
        $oParams = self::getParamByName($sModule, $sName, $sLanguage);

        $sValue = (string) $sValue;

        if (!$oParams) {
            $oParams = new Params();
            $oParams->module = $sModule;
            $oParams->name = $sName;
            $oParams->language = $sLanguage;
        }

        $oParams->value = $sValue;

        return $oParams->save();
    }

    /**
     * Удаляет все параметры категории (модуля).
     *
     * @param $sCategory
     */
    public static function deleteByModule($sCategory)
    {
        Params::deleteAll(['module' => $sCategory]);
    }
}
