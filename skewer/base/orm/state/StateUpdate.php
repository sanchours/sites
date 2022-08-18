<?php

namespace skewer\base\orm\state;

use skewer\base\orm\service;

/**
 * Построитель запросов типа UPDATE
 * Class StateUpdate.
 */
class StateUpdate extends StatePrototype
{
    /**
     * Задание пары (имя поля = значение) для обновления.
     *
     * @param string $sField Имя поля
     * @param null $sValue Значение
     *
     * @return $this
     */
    public function set($sField, $sValue = null)
    {
        $this->addSet($sField, $sValue);

        return $this;
    }

    /**
     * Задание таблицы для обновления записей.
     *
     * @param $mTable
     *
     * @return $this
     */
    public function from($mTable)
    {
        $this->addTableName($mTable);

        return $this;
    }

    /**
     * Добавление выражения в секцию WHERE.
     *
     * @param string $sExpr Выражение
     * @param mixed $mValue Данные
     *
     * @return $this
     */
    public function where($sExpr, $mValue = true)
    {
        $this->addExpr2Section('where', $sExpr, $mValue, service\Storage::SEP_LOGIC_AND);

        return $this;
    }

    /**
     * Добавление выражения в секцию WHERE с разделителем AND.
     *
     * @param string $sExpr Выражение
     * @param bool $mValue Данные
     *
     * @return $this
     */
    public function andWhere($sExpr, $mValue = true)
    {
        return $this->where($sExpr, $mValue);
    }

    /**
     * Добавление выражения в секцию WHERE с разделителем OR.
     *
     * @param string $sExpr Выражение
     * @param bool $mValue Данные
     *
     * @return $this
     */
    public function orWhere($sExpr, $mValue = true)
    {
        $this->addExpr2Section('where', $sExpr, $mValue, service\Storage::SEP_LOGIC_OR);

        return $this;
    }

    /**
     * Добавление условия в секцию ORDER ИН.
     *
     * @param $sField
     * @param string $sWay
     *
     * @return $this
     */
    public function order($sField, $sWay = 'ASC')
    {
        $this->addOrder($sField, $sWay);

        return $this;
    }

    /**
     * Добавление секции LIMIT.
     *
     * @param int $iCount Кол-во элементов в выборке
     * @param int $iShift Смещение выборки
     *
     * @return $this
     */
    public function limit($iCount, $iShift = 0)
    {
        $this->addLimit($iCount, $iShift);

        return $this;
    }

    protected function buildQuery()
    {
        $sQuery = sprintf(
            'UPDATE %s SET %s%s%s%s',
            $this->getBuilder()->getTableName(),
            $this->getBuilder()->getSet(),
            $this->getBuilder()->getWhere(),
            $this->getBuilder()->getOrder(),
            $this->getBuilder()->getLimit()
        );

        return $sQuery;
    }

    protected function getResult()
    {
        return $this->oAdapter->affectedRows();
    }
}
