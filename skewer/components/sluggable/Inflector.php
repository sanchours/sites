<?php

namespace skewer\components\sluggable;

use yii\helpers\Inflector as InflectorBase;

class Inflector extends InflectorBase
{
    /**
     * @param string $string
     * @param string $replacement
     * @param bool $lowercase
     * @return bool|false|string|string[]|null
     * @throws SluggableException
     */
    public static function slug($string, $replacement = '-', $lowercase = true)
    {
        self::checkIntl();
        $string = static::transliterate($string);
        $string = preg_replace('/[^a-zA-Z0-9_=\s—–-]+/u', '', $string);
        $string = preg_replace('/[=\s—–-]+/u', $replacement, $string);
        $string = trim($string, $replacement);

        return $lowercase ? mb_strtolower($string) : $string;
    }

    /**
     * Проверяем, установленно ли расширение intl
     * @throws SluggableException
     */
    public static function checkIntl()
    {
        if (!self::hasIntl()) {
            $sErrorMessage = <<<'ERROR_MESSAGE'
Не установлено расширение Intl. Транслитерация невозможна.<br>
Обратитесь к системному администратору<br>
ERROR_MESSAGE;
            throw new SluggableException($sErrorMessage);
        }
    }
}
