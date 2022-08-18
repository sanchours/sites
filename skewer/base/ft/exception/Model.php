<?php

namespace skewer\base\ft\exception;

use skewer\base\ft as ft;

/**
 * @class: ftModelException
 *
 * @Author: User, $Author$
 * @version: $Revision$
 * @date: $Date$
 */
class Model extends ft\Exception
{
    /**
     * Отдает набор путей для исключения из трассировки.
     *
     * @return array
     */
    protected function getSkipList()
    {
        return [
            RELEASEPATH . 'base/ft/',
        ];
    }
}
