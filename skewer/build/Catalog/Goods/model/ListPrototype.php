<?php

namespace skewer\build\Catalog\Goods\model;

use skewer\base\ft;
use skewer\components\catalog\GoodsSelector;

/**
 * Прототип модели для построения спискового интерфейса для товарных позиций
 * Class ListPrototype.
 */
abstract class ListPrototype
{
    /** @var GoodsSelector Запросник на выборку товаров */
    protected $list;

    /** @var ft\model\Field[] Набор полей, учавствующий в выборках и интерфейсе */
    protected $fields = [];

    /** @var array Фильтр для выборки в формате <имя поля> => <значение> */
    protected $filter = [];

    /** @var int Кол-во позийий на страницу */
    private $onPage = 100;

    /** @var int Страница для показа */
    private $page = 0;

    /** @var int Общее кол-во товаров в выборке */
    private $totalItems = 0;

    public function __construct($sCardName)
    {
        $oModel = ft\Cache::get($sCardName);
        $this->fields = $oModel->getFileds();
        $this->initQuery();
    }

    abstract protected function initQuery();

    /**
     * Получить описание поля из выборки.
     *
     * @param string $sField Имя поля
     *
     * @return ft\model\Field
     */
    public function getField($sField)
    {
        return isset($this->fields[$sField]) ? $this->fields[$sField] : false;
    }

    /**
     * Получить описание всех полей из выборки.
     *
     * @return array|ft\model\Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Список полей для редактирования в списке.
     *
     * @param string[] $out Начальный набор полей
     *
     * @return array
     */
    public function getEditableFields($out = [])
    {
        // поля типа галочка и деньги всегда будут редактируемые
        $aEditableTypes = ['check', 'money'];

        foreach ($this->fields as $field) {
            if (in_array($field->getEditorName(), $aEditableTypes) && (!$field->getAttr('not_edit_field'))) {
                $out[] = $field->getName();
            }
        }

        return $out;
    }

    /**
     * Задает фильтр
     *
     * @param array $aFields Фильтр в формате <имя поля> => <значение>
     *
     * @return $this
     */
    public function setFilter($aFields)
    {
        $this->filter = $aFields;

        return $this;
    }

    /**
     * Получение значения поля фильтра.
     *
     * @param string $sField Имя поля из фильтра
     *
     * @return string
     */
    public function getFilter($sField)
    {
        return isset($this->filter[$sField]) ? $this->filter[$sField] : '';
    }

    /**
     * Применение фильтра к выборке.
     *
     * @return $this
     */
    protected function applyFilter()
    {
        foreach ($this->filter as $field => $val) {
            if ($val) {
                $this->list->fieldCondition($field, $val);
            }
        }

        return $this;
    }

    /**
     * Установка параметров для постраничного просмотра.
     *
     * @param int $page Страница для показа
     * @param int $onPage Кол-во позиций на страницу
     *
     * @return $this
     */
    public function limit($page = 0, $onPage = 100)
    {
        $this->page = $page;
        $this->onPage = $onPage;

        return $this;
    }

    /**
     * Кол-во позийий на страницу.
     *
     * @return int
     */
    public function getOnPage()
    {
        return $this->onPage;
    }

    /**
     * Страница для показа.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Общее кол-во товаров в выборке.
     *
     * @return int
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * Применение ограничений для постраничного просмотра.
     */
    protected function applyLimit()
    {
        if ($this->onPage) {
            $this->list->limit($this->onPage, $this->page + 1);
        }
    }

    /**
     * Получение результатов выборки для товаров.
     *
     * @return mixed
     */
    public function getData()
    {
        $this->applyFilter();
        $this->applyLimit();

        $aItems = $this->list->getArray($this->totalItems);

        // замена у полей-справочников идентификаторров на значения
        foreach ($this->fields as $oField) {
            if (in_array($oField->getEditorName(), ['select', 'selectimage'])) {
                $aRel = $oField->getRelationList();
                if (count($aRel)) {
                    $sRelEntityName = $aRel[0]->getEntityName();
                    $oTable = ft\Cache::getMagicTable($sRelEntityName);
                    foreach ($aItems as &$aItem) {
                        $oItem = $oTable->find($aItem[$oField->getName()]);
                        if ($oItem && isset($oItem->title)) {
                            $aItem[$oField->getName()] = $oItem->title;
                        } else {
                            $aItem[$oField->getName()] = '';
                        }
                    }
                }
            } elseif (in_array($oField->getEditorName(), ['multiselect', 'multiselectimage'])) {
                $subData = [];

                $oRel = $oField->getFirstRelation();

                if (!isset($subData[$oRel->getEntityName()])) {
                    $query = ft\Cache::getMagicTable($oRel->getEntityName())->find()->asArray();

                    while ($row = $query->each()) {
                        $subData[$row['id']] = $row;
                    }
                }

                foreach ($aItems as &$aItem) {
                    $aValues = $oField->getLinkRow($aItem['id']);
                    $sValues = '';
                    if ($aValues) {
                        foreach ($aValues as $iVal) {
                            if (isset($subData[$iVal]['title'])) {
                                $sValues .= ', ' . $subData[$iVal]['title'];
                            }
                        }
                    }
                    $sValues = trim($sValues, ',');
                    $aItem[$oField->getName()] = $sValues;
                }
            } elseif ($oField->getEditorName() == 'check') {
                foreach ($aItems as &$aItem) {
                    $aItem[$oField->getName()] = (bool) $aItem[$oField->getName()];
                }
            }
        }

        return $aItems;
    }
}
