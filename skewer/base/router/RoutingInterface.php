<?php

namespace skewer\base\router;

/**
 * Прототип Vendor`a правил маршрутизации для модулей.
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project Skewer
 */
interface RoutingInterface
{
    /**
     * Возвращает паттерны разбора URL.
     *
     * @static
     *
     * @return bool | array
     */
    public static function getRoutePatterns();
}
