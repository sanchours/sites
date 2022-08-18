<?php

/**
 * Yii bootstrap file.
 * Перекрытие Yii для работы с phpstorm.
 */

/**
 * Class Yii *.
 */
class Yii extends \yii\BaseYii
{
    /**
     * @var \skewer\app\Application the application instance
     */
    public static $app;

    /**
     * Эта штуковина перекрыта для разруливания следующей ситуации:
     * Мультиязычный сайт. Работаем в
     * {@inheritdoc}
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        if (static::$app !== null) {
            return static::$app->getI18n()->translate($category, $message, $params, $language ?: static::$app->i18n->getTranslateLanguage());
        }

        return parent::t($category, $message, $params, $language);
    }

    /**
     * Получить перевод сообщения, заданного строкой в формате <Языковая категория>.<Языковая метка>.
     *
     * @param string $sMessage Строка
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current [[\yii\base\Application::language|application language]] will be used.
     *
     * @return string
     */
    public static function tSingleString($sMessage, $params = [], $language = null)
    {
        if (($iDotPos = mb_strpos($sMessage, '.')) > 0) {
            $sLangKey = mb_substr($sMessage, $iDotPos + 1);
            $sLangVal = self::t(mb_substr($sMessage, 0, $iDotPos), $sLangKey, $params, $language);

            return ($sLangVal == $sLangKey) ? $sMessage : $sLangVal;
        }

        return $sMessage;
    }

    /**
     * Запись в access.log.
     *
     * @param $message
     */
    public static function accessLog($message)
    {
        static::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, 'accessLog');
    }
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = include RELEASEPATH . '../vendor/yiisoft/yii2/classes.php';
Yii::$container = new yii\di\Container();
