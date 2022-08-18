<?php

namespace skewer\base\ui;

use skewer\base\ft;
use skewer\base\ui\form\Field;

/**
 * Класс для построения интерфейса типа "форма"
 * Class Form.
 */
class Form
{
    /**
     * Набор полей.
     *
     * @var Field[]
     */
    protected $aFields = [];

    /**
     * Добавляет поле в список.
     *
     * @param Field $oField
     */
    public function addField(Field $oField)
    {
        $this->aFields[$oField->getName()] = $oField;
    }

    /**
     * Добавляет поле в начало списка.
     *
     * @param Field $oField
     */
    public function prependField(Field $oField)
    {
        array_unshift($this->aFields, $oField);
    }

    /**
     * Добавлние поля по ft модели поля.
     *
     * @param ft\model\Field $oFtField
     */
    public function addByFtField(ft\model\Field $oFtField)
    {
        $this->addField(Field::makeByFt($oFtField));
    }

    /**
     * Добавление полей по ft модели.
     *
     * @param ft\Model $oModel
     */
    public function addByFtModel(ft\Model $oModel)
    {
        foreach ($oModel->getFileds() as $oFtField) {
            $this->addByFtField($oFtField);
        }
    }

    /**
     * Отдает список всех полей.
     *
     * @return Field[]
     */
    public function getFieldList()
    {
        return $this->aFields;
    }

    /**
     * Отдает поле по имени.
     *
     * @param $sName
     *
     * @return null|Field
     */
    public function getField($sName)
    {
        return isset($this->aFields[$sName]) ? $this->aFields[$sName] : null;
    }

    /**
     * Отдает флаг наличия поля по имени.
     *
     * @param string $sName
     *
     * @return bool
     */
    public function hasField($sName)
    {
        return isset($this->aFields[$sName]);
    }
}
