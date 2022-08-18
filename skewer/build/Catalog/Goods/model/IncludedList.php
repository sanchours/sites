<?php

namespace skewer\build\Catalog\Goods\model;

use skewer\components\catalog;

/**
 * Модель для получение списка включенных товаров
 * Class IncludedList.
 */
class IncludedList extends ListPrototype
{
    /** @var int Ид базового товара */
    private $currentObject = 0;

    public function __construct($iGoodsId)
    {
        $this->currentObject = $iGoodsId;
        parent::__construct(catalog\Card::DEF_BASE_CARD);
    }

    /**
     * Функция статической инициализации класса.
     *
     * @param int $iGoodsId Ид базового товара
     *
     * @return static
     */
    public static function get($iGoodsId)
    {
        return new static($iGoodsId);
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
     * Функция инициализации выборки.
     */
    protected function initQuery()
    {
        $this->list = catalog\GoodsSelector::getIncludedList($this->currentObject)
            ->addAvailableSectionField();
    }
}
