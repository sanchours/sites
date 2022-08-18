<?php

namespace skewer\components\ext;

use skewer\build\Cms;

/**
 * Класс для перегрузки нескольких строк в списоковом интерфейсе автопостроителя
 * Служит для изменения / добавления / удаления строк.
 *
 * пока реализовано только изменение
 *
 * Class ExtListRows
 */
class ListRows
{
    /** @var array|string имя поля для сравнения */
    private $mKeyField = '';

    /**
     * Набор строк для добавления / удаления.
     *
     * @var array
     */
    private $aUpdRowList = [];

    /**
     * добавляет нужные параметры к модулю (дополняет посылку на сервер).
     *
     * @param Cms\Tabs\ModulePrototype $oModule
     */
    public function setData(Cms\Tabs\ModulePrototype $oModule)
    {
        $oModule->setData('cmd', 'loadItem');

        $oModule->setData('items', $this->aUpdRowList);
        $oModule->setData('keyField', $this->mKeyField);
    }

    /**
     * Задет поле по которому будут сравниваться строки при обновлении / доб / удалении.
     *
     * @param array|string $mKeyField
     */
    public function setSearchField($mKeyField)
    {
        $this->mKeyField = $mKeyField;
    }

    /**
     * Добавляет набор (строку) данных для изменения / добавления.
     *
     * @param array $aRow
     *
     * @return ListRows
     */
    public function addDataRow($aRow)
    {
        $this->aUpdRowList[] = $aRow;

        return $this;
    }
}
