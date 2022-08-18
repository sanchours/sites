<?php

namespace skewer\build\Page\Forms;

use skewer\base\router\ExclusionTailsInterface;
use skewer\base\router\RoutingInterface;

/**
 * Class Routing.
 */
class Routing implements RoutingInterface, ExclusionTailsInterface
{
    /**
     * Возвращает паттерны разбора URL.
     *
     * @static
     *
     * @return bool | array
     */
    public static function getRoutePatterns()
    {
        return [
            '/*response/',
        ];
    }

    /**
     * Проверяем используется ли в данный момент какой то из паттернов.
     */
    public static function patternUsed()
    {
        foreach (self::getRoutePatterns() as $sInRule) {
            $sInRule = str_replace('*', '', $sInRule);
            $sInRule = str_replace('/', '', $sInRule);
            if (mb_strpos($_SERVER['REQUEST_URI'], $sInRule) !== false) {
                return true;
            }
        }

        return false;
    }

    /** Возвращает правила исключений, допустимых остатков урл, на главной странице  */
    public static function getRulesExclusionTails4MainPage()
    {
        return [
            '/!response/',
        ];
    }
}
