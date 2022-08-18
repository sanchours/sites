<?php

namespace skewer\components\auth\firewall;

/**
 * Прототип сущности.
 *
 * @class FirewallEntity
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project JetBrains PhpStorm
 */
class FirewallEntity
{
    /**
     * Возвращает атрибуты сущности в виде массива.
     *
     * @return array
     */
    public function getDataArray()
    {
        return get_object_vars($this);
    }

    // func
}// class
