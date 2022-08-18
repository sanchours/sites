<?php

namespace skewer\build\Tool\Rss;

use yii\base\Event;

/**
 * Спец класс для сбора записей для rss ленты
 *      через событийную модель.
 */
class GetRowsEvent extends Event
{
    /**
     * @var array aRows[] массив записей для RSS - ленты
     */
    public $aRows = [];
}
