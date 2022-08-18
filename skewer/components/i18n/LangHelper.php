<?php

namespace skewer\components\i18n;

/**
 * Класс для работы с языками в шаблонах Twig, в php файлах не должен быть использован!
 */
class LangHelper
{
    /**
     * Возвращает значение ключа из языковых шаблонов.
     *
     * @param $key
     * @param mixed [$mVal] значения по принциапу sprintf
     *
     * @return string
     */
    public static function get($key)
    {
        if (!$key) {
            return '';
        }

        if (($iPos = mb_strpos($key, '.')) !== false) {
            $sCategory = mb_substr($key, 0, $iPos);
            $sMessage = mb_substr($key, $iPos + 1);

            $aParam = func_get_args();
            array_shift($aParam);

            return \Yii::t($sCategory, $sMessage, $aParam);
        }

        return $key;
    }
}
