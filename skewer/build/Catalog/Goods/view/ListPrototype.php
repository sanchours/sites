<?php

namespace skewer\build\Catalog\Goods\view;

use skewer\base\ui;
use skewer\components\ext;

/**
 * Прототип построителя интерфейса списка товарных позиций
 * Class ListPrototype.
 */
abstract class ListPrototype extends ext\view\ListView
{
    /** @var ui\builder\ListBuilder $_list */
    protected $_list;

    /** @var \skewer\build\Catalog\Goods\model\ListPrototype $model */
    public $model;

    /** @var \skewer\build\Catalog\Goods\Module Модуль в котором осуществляется вывод интерфейса */
    protected $_module;

    private $aData = [];

    /**
     * Установка фильтра по полю для списка.
     *
     * @param string $sField Имя поля для фильтрации
     * @param string $mDefValue Значение поля из фильтра
     * @param string $sType Тип вывода фильтра (TEXT|SELECT)
     * @param array $mData Данные для построения фильтра
     *
     * @throws \Exception
     *
     * @return $this
     */
    protected function addFilter($sField, $mDefValue = '', $sType = 'TEXT', $mData = [])
    {
        if (!$this->_list) {
            throw new \Exception('ListView: ListBuilder not found!');
        }
        $oField = $this->model->getField($sField);

        switch ($sType) {
            case 'TEXT':
                if ($oField && $oField->getAttr('active')) {
                    $this->_list->filterText(
                        'filter_' . $sField,
                        $mDefValue,
                        \Yii::t('catalog', 'filter_' . $sField)
                );
                }
                break;
            case 'SELECT':
                if ($oField && $oField->getAttr('active')) {
                    $this->_list->filterSelect(
                        'filter_' . $sField,
                        $mData,
                        $mDefValue,
                        \Yii::t('catalog', 'filter_' . $sField)
                    );
                }
                break;
        }

        return $this;
    }

    protected function addCustomFilter($sField, $sTitle = '', $mDefValue = '', $sType = 'TEXT', $mData = [])
    {
        if (!$this->_list) {
            throw new \Exception('ListView: ListBuilder not found!');
        }
        switch ($sType) {
            case 'TEXT':
                $this->_list->filterText('filter_' . $sField, $mDefValue, $sTitle);
                break;
            case 'SELECT':
                $this->_list->filterSelect('filter_' . $sField, $mData, $mDefValue, $sTitle);
                break;
        }

        return $this;
    }

    /**
     * Добавление поля в вывод.
     *
     * @param string $sField Имя поля
     * @param string $sType Тип вывода
     * @param array $addData Дополнительные параметры
     * @param string $sTitle Заголовок поля
     *
     * @return $this
     */
    protected function addField($sField, $sType, $addData = [], $sTitle = '')
    {
        if ($sTitle) {
            $this->_list->field($sField, \Yii::tSingleString($sTitle), $sType, ['listColumns' => $addData]);
        } else {
            $oField = $this->model->getField($sField);

            $this->_list->fieldIf(
                $oField and ($oField->getAttr('active') or (strcasecmp($sField, 'id') === 0)),
                $sField,
                $oField ? \Yii::tSingleString($oField->getTitle()) : '',
                $sType,
                ['listColumns' => $addData]
            );
        }

        return $this;
    }

    /**
     * Установка списка редактируемых полей.
     *
     * @param array $aFieldList Список полей
     * @param string $state Состояние в котором происходит сохранение
     *
     * @return $this
     */
    protected function setEditableFields($aFieldList, $state = 'update')
    {
        $this->aData['EditableFields'] = [
            'fields' => $aFieldList,
            'state' => $state,
        ];

        return $this;
    }

    /**
     * Включение сортировки в списке.
     *
     * @param string $state Состояние в котором выполняется сортировка
     *
     * @return $this
     */
    protected function setSorting($state = 'sort')
    {
        $this->_list->enableDragAndDrop($state);

        return $this;
    }

    /**
     * Добавление для каждой записи кнопки типа редактирования.
     *
     * @param string $state Состояние обработки действия
     *
     * @return $this
     */
    protected function btnRowEdit($state = 'edit')
    {
        $this->_list->buttonRowUpdate($state);

        return $this;
    }

    /**
     * Добавление для каждой записи кнопки типа удалить.
     *
     * @param string $state Состояние обработки действия
     *
     * @return $this
     */
    protected function btnRowDelete($state = 'delete')
    {
        $this->_list->buttonRowDelete($state);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function afterBuild()
    {
        $this->_list->setValue(
            $this->model->getData(),
            $this->model->getOnPage(),
            $this->model->getPage(),
            $this->model->getTotalItems()
        );

        if (isset($this->aData['EditableFields'])) {
            $this->_list->setEditableFields(
                $this->aData['EditableFields']['fields'],
                $this->aData['EditableFields']['state']
            );
        }
    }

    /**
     * Установить выделение записи в списке по определённому условию.
     * ВНИМАНИЕ! Работает только для состояния списка!
     *
     * @param string $sFieldName Имя поля
     * @param string $sHint Текст всплывающей подсказки
     * @param array|string $mCondition Условие выделения, которое сравнивается строками со значением поля $sFieldName.
     *                           Допустимо указывать список значений через запятую. По умолчанию условие отрабатывает
     *                           для пустых значений, нулевых и null-значений (маска: ",0,false,null")
     * @param string $sStyle css-стили выделения всей строки списка
     *
     * @return $this
     */
    protected function setHighlighting($sFieldName, $sHint = '', $mCondition = '', $sStyle = 'color: #ff0000')
    {
        $this->_list->setHighlighting($sFieldName, $sHint, $mCondition, $sStyle);

        return $this;
    }
}
