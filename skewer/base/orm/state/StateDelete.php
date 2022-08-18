<?php

namespace skewer\base\orm\state;

use skewer\base\orm\service;

/**
 * Построитель запросов типа DELETE.
 */
class StateDelete extends StatePrototype
{
    /**
     * Задание таблицы для удаления записей.
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
     * @param bool $mValue Данные
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
            'DELETE %s FROM %s%s%s%s',
            '',
            $this->getBuilder()->getTableName(),
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
