<?php

namespace skewer\base\site;

/**
 * Прототип для группы классов, методы которых могут запускаться удаленно.
 */
class ServicePrototype
{
    /**
     * Returns the fully qualified name of this class.
     *
     * @return string the fully qualified name of this class
     */
    public static function className()
    {
        return get_called_class();
    }
}
