<?php

namespace skewer\base\ui\builder;

use skewer\base\ft\Editor;
use skewer\components\ext;
use skewer\components\ext\FormView;

/**
 * Прототип упрощенного сбрщика интерфейсов - форма.
 */
class FormBuilder extends Prototype
{
    const GROUP_TYPE_DEFAULT = 0;
    const GROUP_TYPE_COLLAPSIBLE = 1;
    const GROUP_TYPE_COLLAPSED = 2;

    /** @var ext\FormView */
    protected $oForm;

    /**
     * Конструктор
     *
     * @param null $oInterface
     */
    public function __construct($oInterface = null)
    {
        if ($oInterface === null) {
            $this->oForm = new ext\FormView();
        } else {
            $this->oForm = $oInterface;
        }
    }

    /**
     * Отдает интерфейс формы.
     *
     * @return ext\FormView
     */
    public function getForm()
    {
        return $this->oForm;
    }

    /**
     * Устанавливает флаг использования спец директории для изображений модуля.
     *
     * @param int $iId
     */
    public function useSpecSectionForImages($iId = 0)
    {
        /** @var ext\FormView $form */
        $form = $this->oForm;
        $form->useSpecSectionForImages($iId);
    }

    /**
     * Кастомное поле из пользовательского файла.
     *
     * @param string $sName имя поля
     * @param string $sTitle Название
     * @param string $sClass Имя файла в директории текущего модуля
     * @param mixed $mValue входные данные
     * @param array $aAddParams дополнительные параметры
     *
     * @return $this
     */
    public function fieldSpec($sName, $sTitle, $sClass, $mValue, $aAddParams = [])
    {
        $this->getForm()->addLibClass($sClass);

        $this->oForm->addField(ext\Api::makeFieldObject($aAddParams + [
                'name' => $sName,
                'view' => 'specific',
                'title' => $sTitle,
                'extendLibName' => $sClass,
                'value' => $mValue,
            ]));

        return $this;
    }

    /**
     * Поле для редактирования цвета.
     *
     * @param $name
     * @param $title
     * @param array $params
     *
     * @return $this
     */
    public function fieldColor($name, $title, $params = [])
    {
        $this->setField(
            [
            'name' => $name,
            'type' => 's',
            'view' => Editor::COLOR,
            'title' => $title,
            'default' => '#aaaaaa',
        ],
            $params
        );

        return $this;
    }

    /**
     * Добавить текстовое поле.
     *
     * @param string $sName Имя поля
     * @param string $sTitle Заголовок
     * @param int $iHeight Высота поля
     * @param string $sValue Значение
     * @param array $aParams Дополнительные параметры
     *
     * @return $this
     */
    public function fieldText($sName, $sTitle, $iHeight = 100, $sValue = '', $aParams = [])
    {
        $this->field(
            $sName,
            $sTitle,
            Editor::TEXT,
            [
                'value' => $iHeight,
                'show_val' => $sValue,
            ] + $aParams
        );

        return $this;
    }

    /**
     * Добавить поле html-редактора WYSWYG.
     *
     * @param string $sName Имя поля
     * @param string $sTitle Заголовок
     * @param int $iHeight Высота поля
     * @param string $sValue Значение
     * @param array $aParams Дополнительные параметры
     *
     * @return $this
     */
    public function fieldWysiwyg($sName, $sTitle, $iHeight = 500, $sValue = '', $aParams = [])
    {
        $this->field(
            $sName,
            $sTitle,
            Editor::WYSWYG,
            [
                'value' => $iHeight,
                'show_val' => $sValue,
            ] + $aParams
        );

        return $this;
    }

    /**
     * Добавить поле ссылки.
     *
     * @param string $sName Имя поля
     * @param string $sTitle Заголовок
     * @param string $sText Текст ссылки
     * @param string $sHref Ссылка
     * @param array $aParams Дополнительные параметры
     *
     * @return $this
     */
    public function fieldLink($sName, $sTitle, $sText, $sHref, $aParams = [])
    {
        $this->field(
            $sName,
            $sTitle,
            'link',
            [
                'value' => $sHref,
                'show_val' => $sText,
            ] + $aParams
        );

        return $this;
    }

    /**
     * Добавить поле выпадающего списка.
     *
     * @param string $sName Имя поля
     * @param string $sTitle Заголовок
     * @param array|string $mData Варианты значений в формати строк через двоеточие или массив
     * @param bool $bAddEmpty Флаг добавления пустой записи
     * @param array $aParams Дополнительные параметры
     *
     * @return $this
     */
    public function fieldSelect($sName, $sTitle, $mData, array $aParams = [], $bAddEmpty = true)
    {
        $this->field($sName, $sTitle, Editor::SELECT, [
                'show_val' => FormView::markUniqueValue($mData),
                'emptyStr' => $bAddEmpty,
            ] + $aParams);

        return $this;
    }

    /**
     * Добавляет поле - выпадающий список со множественной выборкой.
     *
     * @param string $sName Имя поля
     * @param string $sTitle Заголовок
     * @param array|string $mData Варианты значений в формати строк через двоеточие или массив
     * @param array|string $mValues Текущие значения
     * @param array $aParams Дополнительные параметры
     *
     * @return $this
     */
    public function fieldMultiSelect($sName, $sTitle, $mData, $mValues = '', array $aParams = [])
    {
        $this->field($sName, $sTitle, Editor::MULTISELECT, [
                'value' => $mValues,
                'show_val' =>  FormView::markUniqueValue($mData),
            ] + $aParams);

        return $this;
    }

    /**
     * Добавить галерейное поле.
     *
     * @param string $sName Имя поля
     * @param string $sTitle Заголовок
     * @param int|string $mProfileId Идентификатор профиля галереи
     * @param array $aParams Дополнительные параметры
     *
     * @return $this
     */
    public function fieldGallery($sName, $sTitle, $mProfileId, $aParams = [])
    {
        $this->field($sName, $sTitle, Editor::GALLERY, ['show_val' => $mProfileId] + $aParams);

        return $this;
    }

    /**
     * Добавление поля в форму.
     *
     * @param string $name Имя поля
     * @param string $title Заголовок поля
     * @param string $editor Тип при выводе
     * @param string $value Значение
     * @param array $params Доп. параметры при выводе
     *
     * @return $this
     */
    public function fieldWithValue($name, $title, $editor, $value, $params = [])
    {
        $tp = 's';

        $this->setField(
            [
            'name' => $name,
            'type' => $tp,
            'view' => $editor,
            'title' => $title,
            'value' => $value,
        ],
            $params
        );

        return $this;
    }

    /**
     * Установка значений для простой формы с одной записью.
     *
     * @param $aItem
     *
     * @return $this
     */
    public function setValue($aItem)
    {
        $this->oForm->setValues($aItem);

        return $this;
    }

    /**
     * Флаг слежения за изменениями в форме
     * #for_form.
     *
     * @param $bTrackChange
     */
    public function setTrackChanges($bTrackChange)
    {
        if ($this->oForm instanceof ext\FormView) {
            $this->oForm->setTrackChanges($bTrackChange);
        }
    }
}
