<?php
/**
 * @author Артем
 * @date 06.03.14
 * @project canape
 */

namespace skewer\build\Tool\ModulesManager;

class Api
{
    /**
     * Возвращает список констант
     *
     * @return array
     */
    public static function getLayers()
    {
        $ref = new \ReflectionClass('\skewer\base\site\Layer');

        return $ref->getConstants();
    }

    /**
     * Возвращает имя слоя в случае если слой $layer существует в списке констант класса Layer, а следовательно,
     * зарегистрирован в системе либо $default в случае, если $layer отсутствует
     *
     * @param string $layer
     * @param $default
     *
     * @return bool
     */
    public static function checkLayer($layer, $default)
    {
        return in_array($layer, self::getLayers()) ? $layer : $default;
    }
}
