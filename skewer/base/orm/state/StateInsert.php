<?php

namespace skewer\base\orm\state;

/**
 * Построитель запросов типа INSERT.
 */
class StateInsert extends StatePrototype
{
    /** @var bool Флаг активности секции ON DUPLICATE KEY UPDATE */
    protected $bDuplicateUpdate = false;

    /**
     * Задание таблицы для вставки записей.
     *
     * @param string $sTable Имя таблицы
     *
     * @return $this
     */
    public function table($sTable)
    {
        $this->addTableName($sTable);

        return $this;
    }

    /**
     * Задание пары (имя поля = значение) для вставки.
     *
     * @param string $sField Имя поля
     * @param null $sValue Значение
     *
     * @return $this
     */
    public function set($sField, $sValue = null)
    {
        if ($this->bDuplicateUpdate) {
            $this->addSetUpdate($sField, $sValue);
        } else {
            $this->addSet($sField, $sValue);
        }

        return $this;
    }

    /**
     * Устанавливает значение поля $sField на 1 больше максимального в выборке.
     *
     * @param string $sField Имя поля
     * @param array $aParams Набор поле-значение для ограничения выборки
     *
     * @return $this
     */
    public function setInc($sField, $aParams = [])
    {
        $this->addSetInc($sField, $aParams);

        return $this;
    }

    /**
     * Установить флаг начала секции ON DUPLICATE KEY UPDATE.
     *
     * @return $this
     */
    public function onDuplicateKeyUpdate()
    {
        $this->bDuplicateUpdate = true;

        return $this;
    }

    protected function buildQuery()
    {
        $aGetSet = $this->getBuilder()->getSet();

        $sQuery = sprintf(
            'INSERT INTO %s SET %s%s',
            $this->getBuilder()->getTableName(),
            $aGetSet,
            (
                $this->bDuplicateUpdate ?
                ' ON DUPLICATE KEY UPDATE ' . $this->getBuilder()->getSet('Update') : $this->getBuilder()->getSetInc()
            )
        );

        return $sQuery;
    }

    protected function getResult()
    {
        $iNewId = (int) $this->oAdapter->lastId();

        return $iNewId ? $iNewId : $this->oAdapter->affectedRows();
    }
}
