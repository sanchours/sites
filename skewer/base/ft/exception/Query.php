<?php

namespace skewer\base\ft\exception;

use skewer\base\ft as ft;

/**
 * @class: Query
 *
 * @Author: kolesnikov, $Author: $
 * @version: $Revision: $
 * @date: $Date: $
 */
class Query extends ft\Exception
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
