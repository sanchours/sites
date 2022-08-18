<?php

namespace skewer\build\Catalog\Goods\model;

use skewer\base\SysVar;
use skewer\components\catalog;

/**
 * Модель для построения спискового интерфейса для товаров раздела
 * Class SectionList.
 */
class SectionList extends ListPrototype
{
    /** @var int Раздел */
    private $section = 0;

    public function __construct($sCardName, $iSectionId)
    {
        $this->section = $iSectionId;
        parent::__construct($sCardName);
    }

    /**
     * Функция статической инициализации класса.
     *
     * @param int $iSectionId Ид раздела для выборки
     *
     * @return static
     */
    public static function get($iSectionId)
    {
        return new static(catalog\Card::DEF_BASE_CARD, $iSectionId);
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

        $this->list->addAvailableSectionField();
    }

    /**
     * Получение результатов выборки.
     *
     * @return mixed
     */
    public function getData()
    {
        $aItems = parent::getData();

        if (SysVar::get('catalog.goods_modifications')) {
            // Дописываем в каждый кортеж кол-во аналогов для товара

            $aKeys = [];
            foreach ($aItems as $key => $aItem) {
                $aKeys[$aItem['id']] = $key;
            }

            if (count($aKeys)) {
                $list = catalog\model\GoodsTable::getChildCount(array_keys($aKeys));

                foreach ($list as $key => $val) {
                    if (isset($aKeys[$key], $aItems[$aKeys[$key]])) {
                        $aItems[$aKeys[$key]]['modifications'] = $val;
                    }
                }
            }
        }

        return $aItems;
    }

    public function getSection()
    {
        return $this->section;
    }
}
