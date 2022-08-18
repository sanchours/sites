<?php

namespace skewer\build\Catalog\Goods\model;

use skewer\components\catalog;

/**
 * Модель для построения кастомного списка всех товаров
 * Используется при выборке товаров для привязки (комплект, связанные)
 * Class FullCustomList.
 */
class FullCustomList extends ListPrototype
{
    /** @var int Основной товар */
    private $currentObject = 0;

    /** @var int Раздел для выборки */
    private $section = 0;

    public function __construct($section = 0)
    {
        $this->section = $section;
        parent::__construct(catalog\Card::DEF_BASE_CARD);
    }

    /**
     * Функция статической инициализации класса.
     *
     * @param $section
     *
     * @return static
     */
    public static function get($section = 0)
    {
        return new static($section);
    }

    /**
     * Получить id основного товара, для которого кастомизируется фильтр
     *
     * @return int
     */
    public function getCurrentObject()
    {
        return $this->currentObject;
    }

    /**
     * Исплючает из выборки товары, которые являются сопутствующими для товарв с ID = $id.
     *
     * @param int $id Ид товарв
     *
     * @return $this
     */
    public function getWithoutRelated($id)
    {
        $this->currentObject = $id;
        $list = [$id];

        $query = catalog\GoodsSelector::getRelatedList($id)->getQuery();

        while ($row = $query->each()) {
            $list[] = $row['id'];
        }

        $this->list->withOut($list)->addAvailableSectionField();

        return $this;
    }

    /**
     * Исплючает из выборки товары, которые идут в комплекте с товаром с ID = $id.
     *
     * @param int $id Ид товарв
     *
     * @return $this
     */
    public function getWithoutIncluded($id)
    {
        $this->currentObject = $id;
        $list = [$id];

        $query = catalog\GoodsSelector::getIncludedList($id)->getQuery();

        while ($row = $query->each()) {
            $list[] = $row['id'];
        }

        $this->list->withOut($list)->addAvailableSectionField();

        return $this;
    }

    /**
     * Функция инициализации выборки.
     */
    protected function initQuery()
    {
        if ($this->section) {
            $this->list = catalog\GoodsSelector::getList4Section($this->section);
        } else {
            $this->list = catalog\GoodsSelector::getList(catalog\Card::DEF_BASE_CARD);
        }
    }
}
