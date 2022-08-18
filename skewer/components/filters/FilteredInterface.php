<?php

namespace skewer\components\filters;

use skewer\base\orm\state\StateSelect;

/**
 * Interface FilteredInterface - интерфейс для отличения полей учавствующих в фильтре.
 */
interface FilteredInterface
{
    /**
     * Добавляет условие фильтра в запрос $oQuery.
     *
     * @param StateSelect $oQuery - запрос
     * @param array $aFilterData - данные фильтра
     *
     * @return bool - true если условие было добавлено, false в противном случае
     */
    public function addFilterConditionToQuery(StateSelect $oQuery, $aFilterData = []);

    /**
     * Возвращает имя виджета поля в фильтре.
     *
     * @return string
     */
    public function getFilterWidgetName();
}
