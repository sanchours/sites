<?php

namespace skewer\build\Catalog\Goods\model;

use skewer\components\catalog;

/**
 * Модель для получения списка модификаций
 * Class ModificList.
 */
class ModificList extends ListPrototype
{
    /** @var int Ид базового товара */
    private $currentObject = 0;

    /** @var catalog\GoodsRow Базовый товар */
    private $curObj;

    /** @noinspection PhpMissingParentConstructorInspection
     * @param int $iGoodsId
     *
     * @throws \Exception
     */
    public function __construct($iGoodsId)
    {
        $this->currentObject = $iGoodsId;
        $this->curObj = catalog\GoodsRow::get($iGoodsId);
        $this->fields = $this->curObj->getFields();
        $this->initQuery();
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
     * @return catalog\GoodsRow
     */
    public function getCurrentObject()
    {
        return $this->curObj;
    }

    /**
     * Функция инициализации выборки.
     */
    protected function initQuery()
    {
        $this->list = catalog\GoodsSelector::getModificationList($this->currentObject);
    }
}
