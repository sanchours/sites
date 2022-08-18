<?php

namespace skewer\components\ext;

use skewer\base\ft as ft;
use skewer\base\orm;
use skewer\base\ui;
use skewer\build\Cms;
use yii\web\ServerErrorHttpException;

/**
 *  Класс для автоматической сборки админских интерфейсов на ExtJS.
 *
 * @class: ExtList
 * @project Skewer
 *
 * @Author: User, $Author$
 * @version: $Revision$
 * @date: $Date$
 */
class ListView extends ModelPrototype implements ui\state\ListInterface
{
    /** Массив колонок по которым разрешено сортировать */
    public $aSortingColumns = [];

    /** action-метод, вызывающийся при сортировки записей колонки */
    public $sort_columns_method;

    /* @var string команда для интерфейса */
    protected $sCmd = '';

    /**
     * Общие Функции.
     */

    /**
     * Возвращает имя компонента.
     *
     * @return string
     */
    public function getComponentName()
    {
        return 'List';
    }

    /**
     * Отдает модель для хранилища.
     *
     * @return array
     */
    protected function getModelForStore()
    {
        $aOut = [];
        foreach ($this->getFields() as $oItem) {
            $aOut[] = [
                'name' => $oItem->getName(),
                'type' => $oItem->getDescVal('storeType'),
            ];
        }

        return $aOut;
    }

    /**
     * Отдает модель для набора колонок.
     *
     * @return array
     */
    protected function getModelForColumns()
    {
        // выходной массив
        $aOut = [];

        // перебрать все строки модели
        foreach ($this->getFields() as $oItem) {
            // собрать строку
            $aOutRow = [
                'text' => $oItem->getTitle(),
                'jsView' => $oItem->getView(),
                'dataIndex' => $oItem->getName(),
            ];

            if ($oItem->hasDescVal('columnType')) {
                $aOutRow['xtype'] = $oItem->getDescVal('columnType');
                $aOutRow['editor'] = $oItem->getDescVal('editor');
            }

            // разрешить сортировку по колонке
            $aOutRow['sortable'] = $this->sortingIsAllowed() && (!$this->aSortingColumns or in_array($oItem->getName(), $this->aSortingColumns));

            // меню колонок отключено
            $aOutRow['menuDisabled'] = true;

            // расширение массива, если есть специальный контейнер
            if ($oItem->hasDescVal('listColumns')) {
                $aOutRow = array_merge($aOutRow, $oItem->getDescVal('listColumns'));
            }

            // добавить в вывод
            $aOut[] = $aOutRow;
        }

        return $aOut;
    }

    /**
     * Задает параметр для передачи в js.
     *
     * @param string $sField имя поля
     * @param string $sParamName имя параметра
     * @param mixed $mParamValue значение параметра
     *
     * @throws ServerErrorHttpException
     */
    public function setFieldParameter($sField, $sParamName, $mParamValue)
    {
        $oField = $this->getField($sField);
        if (!$oField) {
            throw new ServerErrorHttpException("Поле [{$sField}] не определено для отображения");
        }
        // данные кладутся в спец контейнер для спискового интерфейса
        $aData = $oField->getDescVal('listColumns', []);
        $aData[$sParamName] = $mParamValue;
        $oField->setDescVal('listColumns', $aData);
    }

    /**
     * Устанавливает ширину для поля.
     *
     * @param string $sField
     * @param int $iWidth
     */
    public function setFieldWidth($sField, $iWidth)
    {
        $this->setFieldParameter($sField, 'width', $iWidth);
    }

    /**
     * Устанавливает автоматическое расширение для поля (по умолчанию пропорция - 1).
     *
     * @param string $sField
     * @param int $iFlex
     */
    public function setFieldFlex($sField, $iFlex = 1)
    {
        $this->setFieldParameter($sField, 'flex', $iFlex);
    }

    /**
     * Преобразует объект поля в пригодный для ExtJS массив.
     *
     * @param field\Prototype $oItem
     *
     * @return array
     */
    protected function getFieldDesc(field\Prototype $oItem)
    {
        // типы для хранилища
        switch ($oItem->getView()) {
            default:
                $oItem->setDescVal('storeType', 'auto');
                break;
            case 'str':
            case 'text':
            case 'html':
            case 'auto':
            case 'string':
            case 'int':
            case 'float':
            case 'boolean':
            case 'date':
            case 'datetime':
                $oItem->setDescVal('storeType', 'string');
                break;
            case 'hide':
                $oItem->setDescVal('storeType', 'hidden');
                break;
//             * нужен css файл с картинками и обработчик нажатия
//             */
//            case 'check':
//                $aModelRow['storeType'] = 'bool';
//                $aModelRow['columnType'] = 'checkcolumn';
//                $aModelRow['editor'] = array('xtype'=>'checkbox','cls'=>'x-grid-checkheader-editor');
//                break;
        }

        // название - обязательное
        if (!$oItem->getTitle()) {
            $oItem->setTitle($oItem->getName());
        }

        return $oItem->getDesc();
    }

    /**
     * Добавляет к текущей модели запись.
     *
     * @param field\Prototype $oItem новая запись для модели
     *
     * @return bool
     */
    public function addField(field\Prototype $oItem)
    {
        // проверка корректности описания
        if (!$oItem->getName() or !$oItem->getView()) {
            $this->error('Model create. Wrong input.', $oItem->getDesc());

            return false;
        }

        return parent::addField($oItem);
    }

    /**
     * Запрос дополнительных полей для инициализации полей по ft модели.
     *
     * @param ft\model\Field $oField
     *
     * @return array
     */
    protected function getAddParamsForFtField(ft\model\Field $oField)
    {
        return [
            'type' => ExtFT::getLetterType($oField),
            'view' => $this->getView($oField),
        ];
    }

    /**
     * Отдает тип отображения.
     *
     * @param ft\model\Field $oField
     *
     * @return string
     */
    protected function getView(ft\model\Field $oField)
    {
        if ($oField->getEditorName() == 'hide') {
            return 'hide';
        }

        if ($oField->getEditorName() == 'check') {
            return 'check';
        }

        switch ($oField->getDatatype()) {
            case 'varchar':
            default:
                return 'str';
        }
    }

    /**
     * Задает набор полей, которые можно редактировать в списке,
     * а также имя состояния для сохранения
     * Вызывается после добавления полей модели в набор
     *
     * @param string[] $aFieldList набор полей
     * @param string $sCmd имя состояния
     */
    public function setEditableFields($aFieldList, $sCmd = 'save')
    {
        // добавить поля в список
        foreach ($aFieldList as $sFieldName) {
            if ($this->hasField($sFieldName)) {
                $this->getField($sFieldName)->setAddListDesc([
                    'listSaveCmd' => $sCmd,
                ]);
            }
        }
    }

    /**
     * Работа С Данными.
     */

    /**
     * @var array - набор данных для выдачи
     */
    protected $aValues = [];

    /**
     * Задает набор данных для отображения.
     *
     * @param array[]|orm\ActiveRecord[] $aValueList массив наборов данных
     *
     * @throws ServerErrorHttpException
     */
    public function setValues($aValueList)
    {
        // контейнер для сборки финального массива
        $aOutList = [];

        if (!$aValueList) {
            $this->aValues = $aOutList;

            return;
        }

        // перебираем пришедщие строки
        foreach ($aValueList as $aValue) {
            // контейнер для поля
            $aOutVal = [];

            // применение виджетов к полям AR записи
//            if ( $aValue instanceof ft\ArPrototype )
//                $aValue = $aValue->getWidgetValues();

            if ($aValue instanceof orm\ActiveRecord) { // Старый ActiveRecord
                $aValue = $aValue->getData(false);
            }

            if ($aValue instanceof \yii\db\ActiveRecord) {// Новый ActiveRecord
                $aData = $aValue->getAttributes();

                //Перебираем именно поля формы чтобы работали магически __get и __set для AR
                foreach ($this->aFields as $oField) {
                    if (isset($aValue[$oField->getName()])) {
                        $aData[$oField->getName()] = $aValue[$oField->getName()];
                    }
                }

                $aValue = $aData;
            }

            if (!is_array($aValue)) {
                throw new ServerErrorHttpException(
                    'Значения в списке должны быть массивами или экземплярами: ' .
                    ' ft\ArPrototype / db\ActiveRecord / array'
                );
            }

            // перебираем значения, а не модель поскольку они могут дополнительно применяться
            foreach ($aValue as $sName => $mVal) {
                // запросить объект поля
                $oField = $this->getField($sName);

                // если поле
                if ($oField) {
                    // пропустить через него значение
                    $oField->setValue($mVal);
                    $aOutVal[$sName] = $oField->getValueList();

                    if ($oField->getView() == 'int' and $oField->getType() == 'i' and $oField->getValue() !== null) {
                        $aOutVal[$sName] = (int) $aOutVal[$sName];
                    }
                } else {
                    // нет - просто присвоить
                    $aOutVal[$sName] = $mVal;
                }
            }

//
//
//            foreach ( array_diff(array_keys($this->aFields), array_keys($aValue)) as $sFieldName ) {
//
//                var_dump($sFieldName);
//                if ( !isset($aValue[$sFieldName]) )
//                    continue;
//
//
//                // запросить объект поля
//                $oField = $this->getField($sFieldName);
//
//                // пропустить через него значение
//                $oField->setValue( $aValue[$sFieldName] );
//                $aOutVal[$sFieldName] = $oField->getValueList();
//
//                if ( $oField->getView()=='int' and $oField->getType() == 'i' and !is_null($oField->getValue()) )
//                    $aOutVal[$sFieldName] = (int)$aOutVal[$sFieldName];
//
//            }

            // занести полученный результат в выходной массив
            if ($aOutVal) {
                $aOutList[] = $aOutVal;
            }
        }

        $this->aValues = $aOutList;
    }

    /**
     * Отдает набор данных.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->aValues;
    }

    /**
     * Массив для сортировки.
     *
     * @var array
     */
    protected $aSorters = [];

    /**
     * Сбрасывает массив для первичной сортировки.
     */
    public function clearSorters()
    {
        $this->aSorters = [];
    }

    /**
     * Добавить поле сортировки
     * Первичная сортировка при отображении.
     *
     * @param $sFieldName
     * @param string $sDirection
     */
    public function addSorter($sFieldName, $sDirection = 'ASC')
    {
        $this->aSorters[] = [
            'property' => $sFieldName,
            'direction' => $sDirection,
        ];
    }

    /**
     * Возвращает массив полей для сортировки.
     *
     * @return array
     */
    public function getSorters()
    {
        return $this->aSorters;
    }

    /** @var bool Флаг допустимости сортировки по полям */
    protected $aAllowSorting = false;

//    /**
//     * Устанавливает разрешение для сортировки
//     * @param bool $bVal
//     */
//    public function setAllowSorting( $bVal=true ) {
//        $this->aAllowSorting = (bool)$bVal;
//    }

    /**
     * Отдает флаг разрешения сортировки по полям
     *
     * @return bool
     */
    public function sortingIsAllowed()
    {
        if ($this->getDragAndDropActionName()) {
            return false;
        }

        return $this->aAllowSorting;
    }

    /**
     * Разрешает сортировку (заприщает при false), если нет других
     *   управляющих факторов типа DragAndDrop.
     *
     * @param bool $bVal
     */
    public function allowSorting($bVal = true)
    {
        $this->aAllowSorting = (bool) $bVal;
    }

    /**
     * Работа С Постраничным Просмотром
     */

    /**
     * @var int - Всего записей в франилище
     */
    protected $iTotalCnt = 0;

    /**
     * Устанавливает общее число записей в хранилище.
     *
     * @param $iValue - значение
     */
    public function setTotal($iValue)
    {
        $this->iTotalCnt = $iValue;

        /* Если нет записей, то нет и постраничного */
        if (!$iValue) {
            $this->iPage = 0;
            $this->iOnPage = 0;
        }
    }

    /**
     * Возвращает общее число записей в хранилище.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->iTotalCnt;
    }

    /**
     * @var int - число записей на страницу
     */
    protected $iOnPage = 0;

    /**
     * Устанавливает общее число записей на страницу.
     *
     * @param $iValue - значение
     */
    public function setOnPage($iValue)
    {
        $this->iOnPage = $iValue;
    }

    /**
     * Возвращает общее число записей на страницу.
     *
     * @return int
     */
    public function getOnPage()
    {
        return $this->iOnPage;
    }

    /**
     * @var int - номер страницы
     */
    protected $iPage = 0;

    /**
     * Устанавливает номер страницы
     * Счет начинается с 0.
     *
     * @param $iValue - значение
     */
    public function setPageNum($iValue)
    {
        $this->iPage = $iValue;
    }

    /**
     * Возвращает номер страницы.
     *
     * @return int
     */
    public function getPageNum()
    {
        return $this->iPage;
    }

    /**
     * Работа С Кнопками В Строках.
     */
    protected $aRowButtons = [];

    /**
     * Добавляет кнопку к строке.
     *
     * @param $aBtn - описание кнопки
     *
     * @return bool
     */
    public function addRowBtnArray($aBtn)
    {
        $this->aRowButtons[] = $aBtn;

        return true;
    }

    /**
     * Добавляет кнопку к строке.
     *
     * @param ui\element\RowButton $oButton описание кнопки
     */
    public function addRowBtn($oButton)
    {
        $aButton = [
            'tooltip' => $oButton->getTitle(),
            'iconCls' => $oButton->getIcon(),
            'action' => $oButton->getPhpAction(),
            'state' => $oButton->getJsState(),
            'addParams' => $oButton->getAddParamList(),
        ];
        $this->addRowBtnArray($aButton);
    }

    /**
     * Добавляет кастомную js кнопку.
     *
     * @param $sName
     */
    public function addRowCustomBtn($sName)
    {
        $this->addLibClass($sName);
        $this->addRowBtnArray([
            'customBtnName' => $sName,
        ]);
    }

    /**
     * Добавить кнопку "Редактировать".
     *
     * @param string $sAction
     * @param string $sState
     *
     * @return bool
     */
    public function addRowBtnUpdate($sAction = 'show', $sState = 'edit_form')
    {
        return $this->addRowBtnArray([
            'tooltip' => \Yii::t('adm', 'upd'),
            'iconCls' => 'icon-edit',
            'action' => $sAction,
            'state' => $sState,
        ]);
    }

    /**
     * Добавить кнопку "Удалить".
     *
     * @param string $sAction
     * @param string $sState
     *
     * @return bool
     */
    public function addRowBtnDelete($sAction = 'delete', $sState = 'delete')
    {
        return $this->addRowBtnArray([
            'tooltip' => \Yii::t('adm', 'del'),
            'iconCls' => 'icon-delete',
            'action' => $sAction,
            'state' => $sState,
        ]);
    }

    /**
     * Запрашивает набор кнопок для строк.
     *
     * @return array
     */
    public function getRowButtons()
    {
        return $this->aRowButtons;
    }

    /**
     * Варианты Интерфейса.
     */

    /**
     * Отдает только данные для страницы.
     */
    public function actionLoadPage()
    {
        // отключить перезагрузку компонента
        $this->setDoNotReload();

        $this->sCmd = 'loadPage';
    }

    /*
     * Drag'n'Drop
     */

    /**
     * Имя состояния при сортировке.
     *
     * @var string
     */
    protected $sDDAction = '';

    /**
     * @var bool
     */
    protected $bCheckboxSelection = false;

    /**
     * Вывод галочек для множественный операций.
     *
     * @return bool
     */
    public function getCheckboxSelection()
    {
        return $this->bCheckboxSelection;
    }

    /**
     * Активирует сортировку.
     *
     * @param string $sAction имя метода
     */
    public function enableDragAndDrop($sAction)
    {
        $this->setDragAndDropAction($sAction);
    }

    /**
     * Добавляем галочки выбора для операций над множеством записей.
     */
    public function showCheckboxSelection()
    {
        $this->bCheckboxSelection = true;
    }

    /**
     * Убираем галочки выбора для операций над множеством записей.
     */
    public function hideCheckboxSelection()
    {
        $this->bCheckboxSelection = false;
    }

    /**
     * Деактивирует сортировку.
     */
    public function disableDragAndDrop()
    {
        $this->setDragAndDropAction('');
    }

    /**
     * Задает метод для сортировки
     * Если не задан - сортировка не активируется в интерфейсе.
     *
     * @param string $sAction
     */
    public function setDragAndDropAction($sAction)
    {
        $this->sDDAction = $sAction;
    }

    /**
     * Отдает статус активности сортировки.
     *
     * @return bool
     */
    protected function getDragAndDropActionName()
    {
        return $this->sDDAction;
    }

    /**
     * Протокол Передачи Данных.
     */

    /**
     * Собирает интерфейсный массив для выдачи в JS.
     *
     * @return array
     */
    public function getInterfaceArray()
    {
        // выходной массив
        $aOut = [
            'storeModel' => $this->getModelForStore(),
            'columnsModel' => $this->getModelForColumns(),
            'rowButtons' => $this->getRowButtons(),
            'pageNum' => $this->getPageNum(),
            'itemsOnPage' => $this->getOnPage(),
            'itemsTotal' => $this->getTotal(),
            'actionNameLoad' => $this->getPageLoadActionName(),
            'sorters' => $this->getSorters(),
            'sort_columns_method' => $this->sort_columns_method,
            'barElements' => $this->getFilters(),
            'items' => $this->getValues(),
            'cmd' => $this->sCmd,
            'ddAction' => $this->getDragAndDropActionName(),
            'checkboxSelection' => $this->getCheckboxSelection(),
        ];

        // если не надо перегружать страницу
        if ($this->getDoNotReload()) {
            // удалим пустые контейнеры
            foreach ($aOut as $sKey => $mValue) {
                if (empty($mValue)) {
                    unset($aOut[$sKey]);
                }
            }
        }

        // вывод данных
        return $aOut;
    }

    /**
     * Отдает имя контейнера для параметров.
     *
     * @return string
     */
    protected function getParamCont()
    {
        return 'listColumns';
    }

    /**
     * Задает инициализационный  массив для атопостроителя интерфейсов.
     *
     * @param Cms\Frame\ModulePrototype $oModule - ссылка на вызвавший объект
     */
    public function setInterfaceData(Cms\Frame\ModulePrototype $oModule)
    {
        // проверка возможности просмотра постраничного
        //if ( !$oModule->actionExists( $this->getPageLoadActionName() ) )
        //    $this->addError('Задан недоступный для вызова метод постраничного просмотра');

        parent::setInterfaceData($oModule);
    }

    /**
     * Отдает набор полей для вывода по умолчанию.
     *
     * @return string
     */
    protected function getDefaultFieldsSet()
    {
        return 'editor';
    }
}
