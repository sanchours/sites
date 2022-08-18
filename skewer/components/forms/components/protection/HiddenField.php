<?php

namespace skewer\components\forms\components\protection;

class HiddenField implements IProtection
{
    public static $nameHideField = 'cptch_mail';

    /**
     * Возвращает скрытое поле против ботов.
     *
     * @throws \Exception
     * @throws \Throwable
     *
     * @return string
     */
    public static function getHtml()
    {
        $aData = ['name' => self::$nameHideField];

        return \Yii::$app->view->renderPhpFile(
            __DIR__ . '/templates/captchaHide.php',
            $aData
        );
    }

    /**
     * Проверяет заполнено ли скрытое поле cptch_country, если заполнено, то бот
     *
     * @param mixed $aData
     *
     * @return bool
     */
    public static function check($aData = [])
    {
        if (isset($aData[self::$nameHideField]) && $aData[self::$nameHideField] != '') {
            return true;
        }

        return false;
    }
}
