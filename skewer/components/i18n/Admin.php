<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 30.10.2015
 * Time: 17:20.
 */

namespace skewer\components\i18n;

use skewer\base\SysVar;

class Admin
{
    /** метка для хранения текущего языка админки */
    const currentLangLabel = '__cms_curr_lang';

    /**
     * Перекрытие инициализации языков.
     */
    public function initLanguage()
    {
        if (!isset($_SESSION[self::currentLangLabel])) {
            $_SESSION[self::currentLangLabel] = SysVar::get('admin_language');
        }

        \Yii::$app->i18n->setTranslateLanguage($_SESSION[self::currentLangLabel]);
    }

    /**
     * Установка языка.
     *
     * @param $sLangName
     */
    public function setLang($sLangName)
    {
        $_SESSION[self::currentLangLabel] = $sLangName;
        \Yii::$app->i18n->setTranslateLanguage($sLangName);
    }

    /**
     * Сброс языка.
     */
    public function clearLang()
    {
        unset($_SESSION[self::currentLangLabel]);
        \Yii::$app->i18n->setTranslateLanguage(SysVar::get('admin_language'));
    }
}
