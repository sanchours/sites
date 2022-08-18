<?php

namespace skewer\components\ext;

/**
 * Объект для создания новой пусто вкладки.
 */
class EmptyView extends ViewPrototype
{
    /**
     * Возвращает имя компонента.
     *
     * @return string
     */
    public function getComponentName()
    {
        return '';
    }

    /**
     * Отдает интерфейсный массив для атопостроителя интерфейсов.
     *
     * @return array
     */
    public function getInterfaceArray()
    {
        return [];
    }
}
