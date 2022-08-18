<?php

namespace skewer\components\forms;

use skewer\helpers\Files;

class ApiField
{
    const LABEL_POSITION_LEFT = 'left';
    const LABEL_POSITION_RIGHT = 'right';
    const LABEL_POSITION_TOP = 'top';
    const LABEL_POSITION_NONE = 'none';

    /**
     * Возвращает размер максимально допустимого размера для загрузки файлов через формы.
     *
     * @return int
     */
    public static function getUploadMaxSize()
    {
        $iMaxUploadSizeIni = Files::getMaxUploadSize() / 1024 / 1024;
        $iMaxUploadSizeConf = \Yii::$app->getParam(['upload', 'form', 'maxsize']);

        if ($iMaxUploadSizeIni && $iMaxUploadSizeConf) {
            $iMaxUploadSizeConf = $iMaxUploadSizeConf / 1024 / 1024;

            return min($iMaxUploadSizeIni, $iMaxUploadSizeConf);
        }

        return ($iMaxUploadSizeConf) ? $iMaxUploadSizeConf : $iMaxUploadSizeIni;
    }

    /**
     * Обработка дефолтных значений, представленных в виде value:title;
     * и выборка нужного по переданному параметру.
     *
     * @param $sValue
     * @param $sParamDef
     *
     * @return string
     */
    public static function getValueByParamDefault($sValue, $sParamDef)
    {
        $aValues = explode(';', $sParamDef);
        $aNewValues = [];

        foreach ($aValues as &$item2) {
            $aTmp = explode(':', trim($item2));
            if (count($aTmp) == 2) {
                $aNewValues[$aTmp[0]] = $aTmp[1];
            }
        }
        $aTmpData = explode(',', $sValue);

        $aOut = [];
        foreach ($aTmpData as $item2) {
            if (isset($aNewValues[$item2])) {
                $aOut[] = $aNewValues[$item2];
            }
        }
        $sValue = implode(',', $aOut);

        return $sValue;
    }
}
