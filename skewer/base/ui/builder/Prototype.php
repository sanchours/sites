<?php

namespace skewer\base\ui\builder;

use skewer\base\ft\Editor;
use skewer\components\catalog;
use skewer\components\ext;
use skewer\components\gallery\Profile;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

/**
 * Прототип упрощенного сбрщика интерфейсов.
 */
abstract class Prototype
{
    /** @var ext\ModelPrototype */
    protected $oForm;

    /**
     * Отдает интерфейс формы.
     *
     * @return ext\ModelPrototype
     */
    public function getForm()
    {
        return $this->oForm;
    }

    /**
     * Добавление поля на форму из массива параметров.
     *
     * @param array $aFields Массив полей
     * @param array $aParams Массив параметров
     *
     * @throws ServerErrorHttpException
     * @throws \Exception
     */
    protected function setField(array $aFields, array $aParams = [])
    {
        foreach ($aParams as $key => $val) {
            while (($pos = mb_strrpos($key, '.')) !== false) {
                $curKey = mb_substr($key, $pos + 1);
                $newVal = [$curKey => $val];
                $val = $newVal;
                $key = mb_substr($key, 0, $pos);
            }
            $aFields[$key] = $val;
        }

        $this->oForm->addField(ext\Api::makeFieldObject($aFields));
    }

    /**
     * Удалить поле.
     *
     * @param string $sName Имя поля
     */
    public function removeField($sName)
    {
        $this->oForm->removeField($sName);
    }

    /**
     * Получить объект созданного поля.
     *
     * @param $name string Имя поля
     *
     * @return null|ext\field\Prototype
     */
    public function getField($name)
    {
        return $this->oForm->getFields(true)[$name];
    }

    /**
     * Добавление поля в форму.
     *
     * @param string $name Имя поля
     * @param string $title Заголовок поля
     * @param string $editor Тип при выводе
     * @param array $params Доп. параметры при выводе
     *
     * @return $this
     */
    public function field($name, $title, $editor = 'string', $params = [])
    {
        // используется при отображении в списковом интерфейсе для сортировки
        if (in_array($editor, ['int', 'integer', 'num', 'check'])) {
            $tp = 'i';
        } else {
            $tp = 's';
        }

        $this->setField(
            [
            'name' => $name,
            'type' => $tp,
            'view' => $editor,
            'title' => $title,
            'default' => '',
        ],
            $params
        );

        return $this;
    }

    /**
     * Добавление поля в форму, если выполняется условие $bCondition.
     *
     * @param bool $bCondition Флаг добавления поля в форму
     * @param string $sName Имя поля
     * @param string $sTitle Заголовок поля
     * @param string $sViewType Тип при выводе
     * @param array $aAddParams Доп. параметры при выводе
     *
     * @return $this
     */
    public function fieldIf($bCondition, $sName, $sTitle, $sViewType, $aAddParams = [])
    {
        if ($bCondition) {
            $this->field($sName, $sTitle, $sViewType, $aAddParams);
        }

        return $this;
    }

    /**
     * @param catalog\model\FieldRow $field
     * @param array $params
     *
     * @return $this
     */
    public function fieldByEntity(catalog\model\FieldRow $field, $params = [])
    {
        // для галереи передаем дефолтный формат
        if ($field->editor == Editor::GALLERY) {
            $params['gal_profile_id'] = $field->link_id ?: Profile::getDefaultId(Profile::TYPE_CATALOG);
        }

        if ($field->editor == Editor::SELECT) {
            $aDict = catalog\Dict::getValues($field->link_id, 0, true);
            foreach ($aDict as $aFieldDict) {
                $aSelect[$aFieldDict['id']] = $aFieldDict['title'];
            }
            $params['show_val'] = (isset($aSelect)) ? $aSelect : [];
        }
        // если есть группа, передаем ее имя
        if ($field->group) {
            $group = catalog\Card::getGroup($field->group);
            $params['groupTitle'] = ArrayHelper::getValue($group, 'title', '');
        }

        $this->field($field->name, $field->title, $field->editor, $params);

        return $this;
    }

    /**
     * Поле просто отображающее значение без возможности редактирования.
     *
     * @param $name
     * @param $title
     * @param string $tp
     * @param array $params
     *
     * @return $this
     */
    public function fieldShow($name, $title, $tp = 's', $params = [])
    {
        $this->setField(
            [
            'name' => $name,
            'type' => $tp,
            'view' => 'show',
            'title' => $title,
            'default' => '',
        ],
            $params
        );

        return $this;
    }

    /**
     * Скрытое поле.
     *
     * @param $name
     * @param string $title
     * @param string $tp
     * @param array $params
     *
     * @return $this
     */
    public function fieldHide($name, $title = '', $tp = 'i', $params = [])
    {
        $this->setField(
            [
            'name' => $name,
            'type' => $tp,
            'view' => Editor::HIDE,
            'title' => $title,
            'default' => '',
        ],
            $params
        );

        return $this;
    }

    /**
     * Поле типа строка.
     *
     * @param $name
     * @param $title
     * @param array $params
     *
     * @return $this
     */
    public function fieldString($name, $title, $params = [])
    {
        $this->setField(
            [
            'name' => $name,
            'type' => 's',
            'view' => Editor::STRING,
            'title' => $title,
            'default' => '',
        ],
            $params
        );

        return $this;
    }

    /**
     * Поле типа галочка
     * #for_prototype.
     *
     * @param $name
     * @param $title
     * @param array $params
     *
     * @return $this
     */
    public function fieldCheck($name, $title, $params = [])
    {
        $this->setField(
            [
            'name' => $name,
            'type' => 'i',
            'view' => Editor::CHECK,
            'title' => $title,
            'default' => '',
        ],
            $params
        );

        return $this;
    }

    /**
     * Поле типа число.
     *
     * @param $name
     * @param $title
     * @param array $params
     *
     * @return $this
     */
    public function fieldInt($name, $title, $params = [])
    {
        if (!isset($params['allowDecimals'])) {
            $params['allowDecimals'] = false;
        }

        $this->setField(
            [
            'name' => $name,
            'type' => 'i',
            'view' => Editor::INTEGER,
            'title' => $title,
            'default' => '',
        ],
            $params
        );

        return $this;
    }

    /**
     * Фильтр - выпадающий список.
     *
     * @param $sName
     * @param $aValues
     * @param bool $mValue
     * @param string $sTitle
     * @param array $aParams
     *
     * @return $this
     */
    public function filterSelect($sName, $aValues, $mValue = false, $sTitle = '', $aParams = [])
    {
        $this->oForm->addFilterSelect($sName, $aValues, $mValue, $sTitle, $aParams);

        return $this;
    }

    /**
     * Фильтр - дата.
     *
     * @param $sName
     * @param string/array $aValue массив из 2 элементов со значениями (могут содержать false)
     * @param string $sTitle
     * @param array $aParams
     *
     * @return $this
     */
    public function filterDate($sName, $aValue, $sTitle = '', $aParams = [])
    {
        $this->oForm->addFilterDate($sName, $aValue, $sTitle, $aParams);

        return $this;
    }

    /**
     * Фильтр - текст
     *
     * @param string $sName
     * @param string $sValue
     * @param string $sTitle
     *
     * @return $this
     */
    public function filterText($sName, $sValue, $sTitle = '')
    {
        $this->oForm->addFilterText($sName, $sValue, $sTitle);

        return $this;
    }

    /**
     * Добавление кнопки в панель фильтров.
     *
     * @param string $sAction имя метода в php
     * @param string $sTitle надпись на кнопке
     * @param string $sConfirm текст подтверждения, если требуется
     * @param array $aParams дополнительные параметры
     *
     * @return $this
     */
    public function filterButton($sAction, $sTitle, $sConfirm = '', $aParams = [])
    {
        $this->oForm->addFilterButton($sAction, $sTitle, $sConfirm, $aParams);

        return $this;
    }

    /**
     * устанавливает состояние, которое будет вызвано при переходе по страницам
     * по умолчанию будет вызвано состояние "init".
     *
     * @param $sAction
     *
     * @return $this
     */
    public function setFilterAction($sAction)
    {
        $this->oForm->setPageLoadActionName($sAction);

        return $this;
    }

    /**
     * Установка кнопки в интерфейсе.
     *
     * @param $sAction
     * @param $sTitle
     * @param string $sIconCls
     * @param string $sState
     * @param array $aAddParams
     *
     * @return $this
     */
    public function button($sAction, $sTitle, $sIconCls = '', $sState = 'init', $aAddParams = [])
    {
        $aButton = [
            'text' => $sTitle,
            'iconCls' => $sIconCls,
            'state' => $sState,
            'action' => $sAction,
            'skipData' => ($sState == 'new'),
        ];

        if (count($aAddParams)) {
            foreach ($aAddParams as $sKey => $aVal) {
                $aButton[$sKey] = $aVal;
            }
        }

        // отмена подтверждения об отмене сохранения
        if ($sTitle == \Yii::t('adm', 'save')) {
            $aButton['unsetFormDirtyBlocker'] = true;
        }

        $this->oForm->addDockedItem($aButton);

        return $this;
    }

    /**
     * Добавление кнопки в интерфейс при выполнении условия.
     *
     * @param $bCondition
     * @param $sTitle
     * @param $sAction
     * @param string $sIconCls
     * @param string $sState
     * @param array $aAddParams
     *
     * @return $this
     */
    public function buttonIf($bCondition, $sTitle, $sAction, $sIconCls = '', $sState = 'init', $aAddParams = [])
    {
        if ($bCondition) {
            $this->button($sAction, $sTitle, $sIconCls, $sState, $aAddParams);
        }

        return $this;
    }

    /**
     * Кнопка удаления.
     *
     * @param string $action
     * @param null $title
     * @param array $params
     *
     * @return $this
     */
    public function buttonDelete($action = 'delete', $title = null, $params = ['unsetFormDirtyBlocker' => true])
    {
        if ($title === null) {
            $title = \Yii::t('adm', 'del');
        }

        return $this->button($action, $title, 'icon-delete', 'delete', $params);
    }

    /**
     * Добавляет кнопку с подтверждением
     *
     * @param string $sAction событие при нажатии
     * @param string $sTitle надпись на кнопке
     * @param string $sTextConfirm текст подтверждения
     * @param string $sIconCls класс для иконки
     * @param array $aAddParams дополнительные параметры
     *
     * @return $this
     */
    public function buttonConfirm($sAction, $sTitle, $sTextConfirm, $sIconCls = '', $aAddParams = [])
    {
        $aButton = [
            'text' => $sTitle,
            'iconCls' => $sIconCls,
            'state' => 'allow_do',
            'action' => $sAction,
            'actionText' => $sTextConfirm,
            'unsetFormDirtyBlocker' => true,
        ];

        if (count($aAddParams)) {
            foreach ($aAddParams as $sKey => $aVal) {
                $aButton[$sKey] = $aVal;
            }
        }

        $this->oForm->addDockedItem($aButton);

        return $this;
    }

    /**
     * Кнопка "Назад".
     *
     * @param string $sAction
     * @param null $sTitle
     * @param array $aAddParams
     *
     * @return $this
     */
    public function buttonBack($sAction = 'init', $sTitle = null, $aAddParams = [])
    {
        if ($sTitle === null) {
            $sTitle = \Yii::t('adm', 'back');
        }

        return $this->button($sAction, $sTitle, 'icon-cancel', 'init', $aAddParams);
    }

    /**
     * Кнопка "Отмена".
     *
     * @param string $sAction
     * @param null $sTitle
     * @param array $aAddParams
     *
     * @return $this
     */
    public function buttonCancel($sAction = 'init', $sTitle = null, $aAddParams = [])
    {
        if ($sTitle === null) {
            $sTitle = \Yii::t('adm', 'cancel');
        }

        return $this->button($sAction, $sTitle, 'icon-cancel', 'init', $aAddParams);
    }

    /**
     * Кнопка "Редактировать".
     *
     * @param $sAction
     * @param null $sTitle
     * @param array $aAddParams
     *
     * @return $this
     */
    public function buttonEdit($sAction, $sTitle = null, $aAddParams = [])
    {
        if ($sTitle === null) {
            $sTitle = \Yii::t('adm', 'edit');
        }

        return $this->button($sAction, $sTitle, 'icon-edit', 'init', $aAddParams);
    }

    /**
     * Кнопка "Сохранить".
     *
     * @param string $sAction
     * @param null $sTitle
     * @param array $aAddParams
     *
     * @return $this
     */
    public function buttonSave($sAction = 'save', $sTitle = null, $aAddParams = [])
    {
        if ($sTitle === null) {
            $sTitle = \Yii::t('adm', 'save');
        }

        return $this->button($sAction, $sTitle, 'icon-save', 'save', $aAddParams);
    }

    /**
     * Кнопка "Добавить".
     *
     * @param $sAction
     * @param null $sTitle
     * @param array $aAddParams
     *
     * @return $this
     */
    public function buttonAddNew($sAction, $sTitle = null, $aAddParams = [])
    {
        if ($sTitle === null) {
            $sTitle = \Yii::t('adm', 'add');
        }

        return $this->button($sAction, $sTitle, 'icon-add', 'new', $aAddParams);
    }

    /**
     * Добавление разделителя в панеле кнопок.
     *
     * @param string $sLabel
     *
     * @return $this
     */
    public function buttonSeparator($sLabel = '-')
    {
        $this->oForm->addDockedItem($sLabel);

        return $this;
    }

    /**
     * Установка расширенной кнопки в интерфейсе
     * #for_prototype.
     *
     * @param $oButton
     *
     * @return $this
     */
    public function buttonCustomExt(ext\docked\Prototype $oButton)
    {
        $this->oForm->addExtButton($oButton);

        return $this;
    }

    /**
     * Добавить заголовок с текстом
     * #for_prototype.
     *
     * @param string $sText
     *
     * @return $this
     */
    public function headText($sText = '')
    {
        $this->oForm->setAddText($sText);

        return $this;
    }

    /**
     * Добавить определение js библиотеки в вывод.
     * Вызовет при финальной инициализации одноименную функцию админского модуля.
     *
     * @param $sName
     * @param string $sLayerName
     * @param string $sModuleName
     *
     * @return $this
     */
    public function addLib($sName, $sLayerName = '', $sModuleName = '')
    {
        $this->oForm->addLibClass($sName, $sLayerName, $sModuleName);

        return $this;
    }
}
