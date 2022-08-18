<?php

namespace skewer\components\ext;

use skewer\base\ft as ft;
use skewer\base\ui;
use skewer\base\ui\form;

/**
 * Класс прототип для модулей автопостроителя с определением модели.
 */
abstract class ModelPrototype extends ViewPrototype implements ui\state\StateInterface
{
    /* @var string имя состояния загрузки данных */
    protected $actionNameLoad = 'init';

    /** @var field\Prototype[] Модель данных */
    protected $aFields = [];

    /** @var ft\Model Описание модели */
    protected $oModel;

    /**
     * @var array - набор служебных данных
     */
    protected $aFilters = [];

    /**
     * @var bool- флаг: задан ли хоть один фильтр
     */
    protected $bFilterIsSet = false;

    /**
     * Очищает текущую модель данных.
     */
    public function clearFields()
    {
        $this->aFields = [];
    }

    /**
     * Возвращает текущую модель данных.
     *
     * @param bool $bWithNames - с ключами - именами полей
     *
     * @return field\Prototype[]
     */
    public function getFields($bWithNames = false)
    {
        return $bWithNames ? $this->aFields : array_values($this->aFields);
    }

    /**
     * Проверяет наличие поля в модели.
     *
     * @param $sName
     *
     * @return bool
     */
    protected function hasField($sName)
    {
        return isset($this->aFields[$sName]);
    }

    /**
     * Отдает объект поля из модели или null, если нету.
     *
     * @param $sName
     *
     * @return null|field\Prototype
     */
    protected function getField($sName)
    {
        if ($this->hasField($sName)) {
            return $this->aFields[$sName];
        }
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
        // добавление в массив модели
        $this->aFields[$oItem->getName()] = $oItem;

        return true;
    }

    /**
     * устанавливает состояние, которое будет вызвано при переходе по страницам
     * по умолчанию будет вызвано состояние "init".
     *
     * @param string $sActionName
     */
    public function setPageLoadActionName($sActionName)
    {
        $this->actionNameLoad = (string) $sActionName;
    }

    /**
     * Отдает имя состояния для загрузки страницы.
     *
     * @return string
     */
    public function getPageLoadActionName()
    {
        return $this->actionNameLoad;
    }

    /**
     * Работа с фильтрами.
     *
     * @param mixed $sName
     * @param mixed $aValues
     * @param mixed $mValue
     * @param mixed $sTitle
     * @param mixed $aParams
     */

    /**
     * Добавление фильтра - выпадающего списка.
     *
     * @param $sName - системное имя фильтра
     * @param $aValues - массив пар ключ - значение для выпадающего списка
     * @param bool $mValue - текущее значение фильтра
     * @param string $sTitle - название фильтра
     * @param array $aParams - набор дополнительных параметров<br />
     *      default = false // значение по умолчанию<br />
     *      set = false // отключает поле "Все"
     *
     * @return bool
     */
    public function addFilterSelect($sName, $aValues, $mValue = false, $sTitle = '', $aParams = [])
    {
        // пустой селектор добавлять смысла нет
        if (!$aValues) {
            return false;
        }

        // имена библиотек
        $sLibName = 'ListFilterSelect';
        $sLibFullName = $this->getJSLibPrefix() . $sLibName;

        // добавление библиотеки в список загрузок
        $this->addComponent($sLibName);

        // значение отключенной фильтрации
        $aData = [];
        if (!isset($aParams['set']) or !$aParams['set']) {
            $mDefVal = $aParams['default'] ?? false;
            $aData[] = [
                'data' => $mDefVal,
                'text' => \Yii::t('page', 'all'),
                'group' => $sName,
                'checked' => $mValue === $mDefVal,
            ];
        }

        // сборка допустимых значений
        foreach ($aValues as $mKey => $sVal) {
            $aData[] = [
                'data' => $mKey,
                'text' => $sVal,
                'group' => $sName,
                'checked' => $mKey === $mValue,
            ];
        }

        // установка флага заданность значений в фильтрах
        if (!$this->bFilterIsSet and $mValue !== false) {
            $this->bFilterIsSet = true;
        }

        // добавление записи в список фильтров
        $this->aFilters[] = [
            'libName' => $sLibFullName,
            'fieldName' => $sName,
            'title' => $sTitle,
            'text' => $sTitle,
            'menu' => [
                'data' => $sName,
                'items' => $aData,
            ],
        ];

        return true;
    }

    /**
     * Добавление кнопки в панель фильтров.
     *
     * @param string $sAction имя метода в php
     * @param string $sTitle надпись на кнопке
     * @param string $sConfirm текст подтверждения, если требуется
     * @param array $aParams дополнительные параметры
     *
     * @return bool
     */
    public function addFilterButton($sAction, $sTitle, $sConfirm = '', $aParams = [])
    {
        // имена библиотек
        $sLibName = 'ListFilterButton';
        $sLibFullName = $this->getJSLibPrefix() . $sLibName;

        // добавление библиотеки в список загрузок
        $this->addComponent($sLibName);

        // имя метода кладем в список
        $aParams['cmd'] = $sAction;

        // добавление кнопки в панель управления
        $this->aFilters[] = [
            'libName' => $sLibFullName,
            'text' => $sTitle,
            'textConfirm' => $sConfirm,
            'addParams' => $aParams,
        ];

        return true;
    }

    /**
     * Добавление фильтра - текстового поля.
     *
     * @param string $sName имя фильтра
     * @param string $sValue значение по умолчанию
     * @param string $sTitle название фильтра
     *
     * @return bool
     */
    public function addFilterText($sName, $sValue = '', $sTitle = '')
    {
        // установка флага "заданность значений в фильтрах"
        if (!$this->bFilterIsSet and $sValue !== '') {
            $this->bFilterIsSet = true;
        }

        // имена библиотек
        $sLibName = 'ListFilterText';
        $sLibFullName = $this->getJSLibPrefix() . $sLibName;

        // добавление библиотеки в список загрузок
        $this->addComponent($sLibName);

        // добавление кнопки в панель управления
        $this->aFilters[] = [
            'libName' => $sLibFullName,
            'emptyText' => $sTitle,
            'fieldValue' => $sValue,
            'fieldName' => $sName,
        ];

        return true;
    }

    /**
     * Добавление фильтра по дате.
     *
     * @param string $sName имя фильтра
     * @param string/array $aValue массив из 2 элементов со значениями (могут содержать false)
     * @param string $sTitle название
     * @param array $aParams дополнительные параметры
     *
     * @return bool
     */
    public function addFilterDate($sName, $aValue, $sTitle = '', $aParams = [])
    {
        // установка флага "заданность значений в фильтрах"
        if (!$this->bFilterIsSet and $aValue and is_array($aValue)) {
            if ((isset($aValue[0]) and $aValue[0]) or (isset($aValue[1]) and $aValue[1])) {
                $this->bFilterIsSet = true;
            }
        }

        // имена библиотек
        $sLibName = 'ListFilterDate';
        $sLibFullName = $this->getJSLibPrefix() . $sLibName;

        // добавление библиотеки в список загрузок
        $this->addComponent($sLibName);

        // добавление кнопки в панель управления
        $this->aFilters[] = [
                'libName' => $sLibFullName,
                'title' => $sTitle,
                'fieldValue' => $aValue,
                'fieldName' => $sName,
            ] + $aParams;

        return true;
    }

    /**
     * Возвращает набор фильтров для состояния.
     *
     * @return array
     */
    protected function getFilters()
    {
        return $this->aFilters;
    }

    /**
     * Устанавливает модель данных для списка.
     *
     * @param $aItems - новое описание модели
     */
    public function setFields($aItems)
    {
        // очищаем текеущую модель
        $this->clearFields();

        // перебираем пришедшие данные
        foreach ($aItems as $aItemRow) {
            // добавить поле
            $this->addField(Api::makeFieldObject($aItemRow));

            // если есть кастомные поля
            if (is_array($aItemRow)) {
                if (isset($aItemRow['customField'])) {
                    $this->addLibClass($aItemRow['customField']);
                }
            }
        }
    }

    /**
     * Отдает набор полей для вывода по умолчанию.
     *
     * @return string
     */
    abstract protected function getDefaultFieldsSet();

    /**
     * Задает набор полей по FT модели
     *  Для передачи параметров в js можно использовать префиксы list_ / show_ / form_ для
     *      соответствующих состояний. Напромер для растягивания поля в списке можно написать
     *      ->parameter( 'list_flex', 1 ) (для ft\Entity или setParameter для ft\Model).
     *
     * @param ft\Model $oModel описание модели
     * @param array|string $mColSet набор колонок для вывода
     */
    public function setFieldsByFtModel(ft\Model $oModel, $mColSet = '')
    {
        $aOutModel = [];

        // набор колонок для отображения
        if (func_num_args() === 1) {
            $mColSet = $this->getDefaultFieldsSet();
        }

        if (is_array($mColSet)) {
            $aFields = $mColSet;
        } else {
            $aFields = $oModel->getColumnSet($mColSet);
        }

        if (!$mColSet || empty($aFields)) {
            $aFields = $oModel->getAllFieldNames();
        }

        // префикс параметров
        $sPrefix = $this->getParamPrefix();

        /** @var string $sPCont имя контейнера для параметров */
        $sPCont = $this->getParamCont();

        // переобразуем объект в массив полей
        foreach ($aFields as $sFieldName) {
            $oField = $oModel->getFiled($sFieldName);

            if (!$oField) {
                continue;
            }

            $aParams = array_merge([
                'name' => $oField->getName(),
                'title' => \Yii::tSingleString($oField->getTitle()),
                'view' => $oField->getEditorName() ? $oField->getEditorName() : $oField->getDatatype(), // Установка editor для поля
            ], $this->getAddParamsForFtField($oField));

            // добавление параметров по шаблону
            foreach ($oField->getParameterList() as $sParamName => $mParamVal) {
                if (mb_strpos($sParamName, $sPrefix) === 0) {
                    if ($sPCont) {
                        $aParams[$sPCont][mb_substr($sParamName, mb_strlen($sPrefix))] = $mParamVal;
                    } else {
                        $aParams[mb_substr($sParamName, mb_strlen($sPrefix))] = $mParamVal;
                    }
                }
            }

            // создаем объект из массивов
            $oIfaceField = ExtFT::createFieldByFt($aParams, $oField, $oModel);

            $aOutModel[$oField->getName()] = $oIfaceField;
        }

        // сохраняем ссылку на используемую модель в построителе
        $this->oModel = $oModel;

        $this->setFields($aOutModel);
    }

    /**
     * Задает набор полей по объектному списку полей.
     *
     * @param Form $oForm описание модели
     */
    public function setFieldsByUiForm(Form $oForm)
    {
        $aOutModel = [];

        // переобразуем объект в массив полей
        foreach ($oForm->getFieldList() as $oField) {
            $aParams = array_merge([
                'name' => $oField->getName(),
                'title' => $oField->getTitle(),
                'view' => $oField->getEditor(),
            ], $oField->getOutParamList());

            // создаем объект из массивов
            $oIfaceField = ExtFT::createFieldByUi($aParams, $oField);

            $aOutModel[$oField->getName()] = $oIfaceField;
        }

        $this->setFields($aOutModel);
    }

    /**
     * Отдает имя контейнера для параметров.
     *
     * @return string
     */
    protected function getParamCont()
    {
        return '';
    }

    /**
     * Отдает префикс параметра для сборки модели.
     *
     * @return string
     */
    protected function getParamPrefix()
    {
        $sVal = $this->getComponentName();

        return $sVal ? mb_strtolower($sVal) . '_' : '';
    }

    /**
     * Запрос дополнительных полей для инициализации полей по ft модели.
     *
     * @param ft\model\Field $oField
     *
     * @return array
     */
    protected function getAddParamsForFtField(/* @noinspection PhpUnusedParameterInspection */ft\model\Field $oField)
    {
        return [];
    }

    /**
     * Удалить поле.
     *
     * @param string $sName Имя поля
     */
    public function removeField($sName)
    {
        unset($this->aFields[$sName]);
    }
}
