<?php

namespace skewer\components\design;

/**
 * Прототип для переключателя подвала сайта.
 */
abstract class TplSwitchFooter extends TplSwitchPrototype
{
    public $sPathDir = '';

    /**
     * Отдает тип переключателя шаблонов.
     *
     * @return string
     */
    protected function getType()
    {
        return 'footer';
    }

    /**
     * Для подвала этот метод нельзя перекрывать,
     * поскольку у него нет собственной зоны вывода.
     */
    final protected function getModulesList()
    {
        return '';
    }

    /**
     * Заменяет набор модулей для сайта
     * Отключен для подвала, поскольку нет своей зоны вывода.
     */
    final public function setModules()
    {
    }
}
