<?php

namespace skewer\components\config;

/**
 * Класс с константами для путей в конфигах.
 */
class Vars
{
    /** корневая запись со слоями */
    const LAYERS = 'layers';

    /** набор модулей */
    const MODULES = 'modules';

    /** константа событий */
    const EVENTS = 'events';

    /** имя поля с классом для события - класс из которого будем вызывать */
    const EVENT_CLASS = 'class';

    /** имя поля с методом для события (из класса EVENT_CLASS) */
    const EVENT_METHOD = 'method';

    /** имя поля с названием события */
    const EVENT_NAME = 'event';

    /**
     * имя поля с классом на который вешается событие (не путать с EVENT_CLASS.)
     *  - это класс, событие которого прослушивается.
     */
    const EVENT_TO_CLASS = 'eventClass';

    /** функциональные политики */
    const POLICY = 'policy';

    /** функциональные политики - контейнер набора параметров */
    const POLICY_ITEMS = 'items';

    /** функциональные политики */
    const POLICY_TITLE = 'title';

    /** функциональные политики - имя параметра */
    const POLICY_VAL_NAME = 'name';

    /** функциональные политики - значение по умолчанию */
    const POLICY_VAL_DEFAULT = 'default';

    /** имя слоя */
    const LAYER_NAME = 'layer';

    /** имя модуля */
    const MODULE_NAME = 'module';

    /** константа для сборщика мусора */
    const CLEANUP = 'cleanup';

    const CLEANUP_TO_CLASS = 'cleanupClass';
}
