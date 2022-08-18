<?php

namespace skewer\build\Catalog\Goods\model;

use skewer\build\Adm\Order\ar\Goods;
use skewer\components\catalog;

/**
 * Модель для построения списка всех товаров
 * Используется при выборке товаров для добавления в админке товаров к заказу
 * Class AllGoodsList.
 */
class GoodsForOrderEditorList extends ListPrototype
{
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
     * Исплючает из выборки товары, которые уже есть в заказе.
     *
     * @param int $id id заказа
     *
     * @return $this
     */
    public function getWithoutOrderedGoods($id)
    {
        $aRow = Goods::find()
            ->where('id_order', $id)
            ->fields('id_goods')
            ->asArray()
            ->getAll();

        $list = array_column($aRow, 'id_goods');

        if ($list && !empty($list)) {
            $this->list->withOut($list);
        }

        return $this;
    }

    /**
     * Функция инициализации выборки.
     */
    protected function initQuery()
    {
        $this->list = catalog\GoodsSelector::getList(catalog\Card::DEF_BASE_CARD, false)->condition('active', 1);
    }
}
