<?php

namespace skewer\base\site_module;

interface ExcludedParametersInterface
{
    /** @const string Имя интерфейса */
    const interfaceName = '\skewer\base\site_module\ExcludedParametersInterface';

    /**
     * Получить параметры, исключенные из показа в редакторе.
     *
     * @return array
     */
    public static function getExcludedParameters();
}
