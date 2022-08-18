<?php

namespace skewer\base\ui\builder;

use skewer\components\ext;

/**
 * Прототип упрощенного сбрщика интерфейсов - форма.
 */
class ListBuilder extends Prototype
{
    /** @var ext\ListView */
    protected $oForm;

    private $aWidgetList = [];

    /**
     * Конструктор
     *
     * @param null $oInterface
     */
    public function __construct($oInterface = null)
    {
        if ($oInterface === null) {
            $this->oForm = new ext\ListView();
        } else {
            $this->oForm = $oInterface;
        }
    }

    /**
     * Отдает интерфейс формы.
     *
     * @return ext\ListView
     */
    public function getForm()
    {
        return $this->oForm;
    }

    /**
     * Установка значений для формы.
     *
     * @param $mValue
     * @param int $iOnPage Кол-во на странице
     * @param int $iPage Страница
     * @param int $iTotal Всего записей
     *
     * @return $this
     * @throws \yii\web\ServerErrorHttpException
     */
    public function setValue($mValue, $iOnPage = 0, $iPage = 0, $iTotal = 0)
    {
        $aFieldsTypes = [];
        $aFields = $this->oForm->getFields(true);
        foreach ($aFields as $sKey => $aField) {
            if ($aField->getType() == 'i') {
                $aFieldsTypes[] = $sKey;
            }
        }

        $aList = [];

        if (($this->oForm instanceof ext\ListView) && is_array($mValue)) {
            foreach ($mValue as $oItem) {
                if (count($this->aWidgetList)) {
                    foreach ($this->aWidgetList as $aWidget) {
                        $sField = $aWidget['field'];
                        if (class_exists($aWidget['class'])) {
                            if (is_object($oItem)) {
                                $oItem->{$sField} = call_user_func_array([$aWidget['class'], $aWidget['method']], [$oItem, $sField]);
                            } elseif (is_array($oItem)) {
                                $oItem[$sField] = call_user_func_array([$aWidget['class'], $aWidget['method']], [$oItem, $sField]);
                            }
                        }
                    }
                }

                foreach ($aFieldsTypes as $sFieldName) {
                    if (is_object($oItem)) {
                        $oItem->{$sFieldName} = (int) $oItem->{$sFieldName};
                    } else {
                        $oItem[$sFieldName] = (int) $oItem[$sFieldName];
                    }
                }

                $aList[] = $oItem;
            }
        } else {
            $aList = $mValue;
        }

        $this->oForm->setValues($aList);

        if ($iOnPage) {
            // число записей на страницу
            $this->oForm->setOnPage($iOnPage);
            $this->oForm->setPageNum($iPage);

            // #for_paginator нельзя использовать count($aItems)
            if ($iTotal) {
                $this->oForm->setTotal($iTotal);
            } else {
                $this->oForm->setTotal(count($mValue));
            }
        }

        return $this;
    }

    /**
     * Кнопка редактировать в строке.
     *
     * @param string $sAction Состояние в PHP
     * @param string $sState Состояние в JS
     *
     * @return $this
     */
    public function buttonRowUpdate($sAction = 'show', $sState = 'edit_form')
    {
        $this->oForm->addRowBtnArray([
            'tooltip' => \Yii::t('adm', 'upd'),
            'iconCls' => 'icon-edit',
            'action' => $sAction,
            'state' => $sState,
        ]);

        return $this;
    }

    /**
     * Кнопка удалить в строке.
     *
     * @param string $sAction Состояние в PHP
     * @param string $sState Состояние в JS
     * @param string $sText actionText
     *
     * @return $this
     */
    public function buttonRowDelete($sAction = 'delete', $sState = 'delete', $sText = '')
    {
        $this->oForm->addRowBtnArray([
            'tooltip' => \Yii::t('adm', 'del'),
            'iconCls' => 'icon-delete',
            'action' => $sAction,
            'state' => $sState,
            '' => $sText,
        ]);

        return $this;
    }

    /**
     * Установка кнопки в интерфейсе для работы с несколькими записями.
     *
     * @param $sTitle
     * @param $sAction
     * @param string $sIconCls
     * @param string $sState
     * @param array $aAddParams
     *
     * @return $this
     */
    public function buttonMultiple($sAction, $sTitle, $sIconCls = '', $sState = 'init', $aAddParams = [])
    {
        $aAddParams['multiple'] = true;

        return $this->button($sAction, $sTitle, $sIconCls, $sState, $aAddParams);
    }

    /**
     * Установка кнопки в интерфейсе для удаления нескольких записей.
     *
     * @param $sAction
     * @param string $sState
     * @param $sTitle
     * @param array $aAddParams
     *
     * @return $this
     */
    public function buttonDeleteMultiple($sAction = 'delete', $sState = 'delete', $sTitle = '', $aAddParams = [])
    {
        $aAddParams['multiple'] = true;
        if (!$sTitle) {
            $sTitle = \Yii::t('adm', 'del');
        }

        return $this->button($sAction, $sTitle, 'icon-delete', $sState, $aAddParams);
    }

    /**
     * Установка кнопки в интерфейсе для добавления нескольких записей.
     *
     * @param $sAction
     * @param string $sState
     * @param $sTitle
     * @param array $aAddParams
     *
     * @return $this
     */
    public function buttonAddNewMultiple($sAction = 'add', $sState = 'add', $sTitle = '', $aAddParams = [])
    {
        $aAddParams['multiple'] = true;
        if (!$sTitle) {
            $sTitle = \Yii::t('adm', 'add');
        }

        return $this->button($sAction, $sTitle, 'icon-add', $sState, $aAddParams);
    }

    /**
     * Кнопка в списке с подтверждением
     *
     * @param $sAction
     * @param $sTitle
     * @param $sTextConfirm
     * @param $sIcon
     * @param string $sState
     *
     * @return $this
     */
    public function buttonRowConfirm($sAction, $sTitle, $sTextConfirm, $sIcon, $sState = 'allow_do')
    {
        $aButton = [
            'tooltip' => $sTitle,
            'iconCls' => $sIcon,
            'action' => $sAction,
            'state' => $sState,
            'actionText' => $sTextConfirm,
            'unsetFormDirtyBlocker' => true,
        ];
        $this->oForm->addRowBtnArray($aButton);

        return $this;
    }

    /**
     * Добавляет кнопку к строке.
     *
     * @param string $sAction состояние при нажатии
     * @param string $sTitle
     * @param string $sIcon
     * @param string $sState
     *
     * @return $this
     */
    public function buttonRow($sAction, $sTitle, $sIcon, $sState = '')
    {
        $this->oForm->addRowBtnArray([
            'tooltip' => $sTitle,
            'iconCls' => $sIcon,
            'action' => $sAction,
            'state' => $sState,
        ]);

        return $this;
    }

    /**
     * Добавляет кнопку, описанную специфическим js классом.
     * Автоматически подгружается js библиотека.
     * Может использоваться библиотека из другого модуля (по умолчанию тянется из текущего).
     *
     * @param $sName
     * @param string $sLayerName слой
     * @param string $sModuleName имя модуля
     * @param array $aParams дополнительные параметры для передачи
     *
     * @return $this
     */
    public function buttonRowCustomJs($sName, $sLayerName = '', $sModuleName = '', $aParams = [])
    {
        $this->oForm->addLibClass($sName, $sLayerName, $sModuleName);

        $aParams['customBtnName'] = $sName;
        $aParams['customLayer'] = $sLayerName;

        $this->oForm->addRowBtnArray($aParams);

        return $this;
    }

    /**
     * Кнопка добавить в строке.
     *
     * @param string $sAction Состояние в PHP
     * @param string $sState Состояние в JS
     *
     * @return $this
     */
    public function buttonRowAddNew($sAction = 'add', $sState = 'add')
    {
        $this->oForm->addRowBtnArray([
            'tooltip' => \Yii::t('adm', 'add'),
            'iconCls' => 'icon-add',
            'action' => $sAction,
            'state' => $sState,
        ]);

        return $this;
    }

    /**
     * Добавление нового преобразователя на поле.
     *
     * @param string $sFieldName имя поля
     * @param string $sClassName класс обработчик
     * @param string $sMethod метод в классе
     *
     * @return $this
     */
    public function widget($sFieldName, $sClassName, $sMethod)
    {
        $this->aWidgetList[] = [
            'field' => $sFieldName,
            'class' => $sClassName,
            'method' => $sMethod,
        ];

        return $this;
    }

    /**
     * Задает настройки группировки для списка.
     *
     * @param string $sFieldName Имя поля, содержащее заголовок группы
     * @param bool $bAllowSorting Разрешить межгрупповую сортировку (перетаскивание)
     * @param bool $bCollapsed Свернуть группы
     * @param array $aOpenedGroups Массив заголовоков групп, которые будут открыты
     *
     * @return $this
     */
    public function setGroups($sFieldName, $bAllowSorting = false, $bCollapsed = false, array $aOpenedGroups = [])
    {
        $this->oForm->setInitParam('groupslist_groupField', $sFieldName);
        $this->oForm->setInitParam('groupslist_allowSorting', $bAllowSorting);
        $this->oForm->setInitParam('groupslist_startCollapsed', $bCollapsed);
        $aOpenedGroups and $this->oForm->setInitParam('groupslist_openedGroups', '|' . implode('|', $aOpenedGroups) . '|');

        return $this;
    }

    /**
     * Включение DnD в списке.
     *
     * @param $sActionName
     *
     * @return $this
     */
    public function enableDragAndDrop($sActionName)
    {
        $this->oForm->enableDragAndDrop($sActionName);

        return $this;
    }

    /**
     * Включение галочек выбора для операций над множеством записей списке.
     *
     * @return $this
     */
    public function showCheckboxSelection()
    {
        $this->oForm->showCheckboxSelection();

        return $this;
    }

    /**
     * Выключение галочек выбора для операций над множеством записей списке.
     *
     * @return $this
     */
    public function hideCheckboxSelection()
    {
        $this->oForm->hideCheckboxSelection();

        return $this;
    }

    /**
     * Добавление редактируемых полей в списке.
     *
     * @param array $aFieldList Список полей
     * @param string $sAction Метод обработки
     *
     * @throws
     *
     * @return $this
     */
    public function setEditableFields(array $aFieldList, $sAction)
    {
        $fields = $this->oForm->getFields();

        if (empty($fields)) {
            throw new \Exception('setEditableFields вызван раньше setFields');
        }

        $this->oForm->setEditableFields($aFieldList, $sAction);

        return $this;
    }

    /**
     * Добавить поле сортировки
     * Первичная сортировка при отображении.
     *
     * @param $sFieldName
     * @param string $sDirection
     *
     * @return $this
     */
    public function sortBy($sFieldName, $sDirection = 'ASC')
    {
        $this->oForm->addSorter($sFieldName, $sDirection);

        return $this;
    }

    /**
     * Разрешает сортировку по клику на заголовке колонок в списковом интерфейсе,
     * если нет других управляющих факторов типа DragAndDrop.
     *
     * @param array $aColumns Список колонок для сортировки. Пустой массив -- все
     * @param string $sMethodName Имя action-метода события сортировки. Актуально при сортировки списков с постраничником
     *
     * @return $this
     */
    public function enableSorting(array $aColumns = [], $sMethodName = '')
    {
        $this->oForm->aSortingColumns = $aColumns;
        $this->oForm->sort_columns_method = $sMethodName;
        $this->oForm->allowSorting(true);

        return $this;
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
    public function setHighlighting($sFieldName, $sHint = '', $mCondition = '', $sStyle = 'color: #ff0000')
    {
        $sCondition = is_array($mCondition) ? implode(',', $mCondition) : $mCondition;
        $aHighlightingParam = $this->oForm->getInitParam('highlighting_list_item') ?: [];
        $aHighlightingParam[$sFieldName] = [
            'hint' => $sHint,
            'condition' => $sCondition ? "{$sCondition}" : ',0,false,null',
            'style' => $sStyle,
        ];

        $this->oForm->setInitParam('highlighting_list_item', $aHighlightingParam);

        return $this;
    }

    /**
     * Задает набор языковых меток для модуля.
     *
     * @param array $aKeys набор псевдонимов языковых меток
     *
     * @return array
     */
    public function setModuleLangValues($aKeys)
    {
        $this->oForm->setInitParam('lang', $aKeys);
    }
}
