<?php

namespace skewer\base\router;

/**
 * Данный интерфейс реализуют сущности, предусматривающие допустимые остатки url
 * Interface ExclusionTailsInterface.
 */
interface ExclusionTailsInterface
{
    /** Возвращает правила исключений, допустимых остатков урл, на главной странице  */
    public static function getRulesExclusionTails4MainPage();
}
