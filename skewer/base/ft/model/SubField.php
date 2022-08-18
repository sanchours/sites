<?php

namespace skewer\base\ft\model;

use skewer\base\ft as ft;

/**
 * Модель данных для поля подчиненной сущности.
 */
class SubField extends Field
{
    /**
     * Возвращает true, если поле - подчиненная сущность.
     *
     * @return bool
     */
    public function isEntity()
    {
        return true;
    }

    /** @var ft\Model объет описание подчиненной сущности */
    protected $oModel;

    /**
     * Отдает имя сущности.
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->oModel->getName();
    }

    /**
     * Конструктор
     *
     * @param string $sFieldName имя поля
     * @param ft\Model $oModel
     * @param string $sConnectionType
     * @param string $sAltTitle
     *
     * @throws \skewer\base\ft\Exception
     */
    public function __construct($sFieldName, ft\Model $oModel, $sConnectionType = '', $sAltTitle = '')
    {
        // сохраняем описание
        $this->oModel = $oModel;

        // имя сущности
        $this->sName = (string) $sFieldName;
        if (!$this->sName) {
            throw new ft\Exception('Отсутствует имя поля');
        }
        // название сущности
        $this->sTitle = $sAltTitle ? $sAltTitle : $this->getEntityName();

        // запись типа cвязи в сущность
        if ($sConnectionType) {
            $oModel->setConnectionType($sConnectionType);
        }

        // добавление записи о содержащем поле в сущность
        $oModel->setParentField($sFieldName);

        // связь типа МкМ
        if ($sConnectionType === '><') {
            $oModel->setSourceEntity($this->getEntityName());
        }

        // жесткая связь типа 1к1
        if ($sConnectionType === '---') {
            $oModel->setParentEntity($this->getEntityName());
            $mSubEntity = ft\Entity::get($this->getEntityName());
            $mSubEntity
                ->setParentEntity($this->getEntityName())
                ->setParentField($sFieldName)
                ->setConnectionType('---')
                ->save();
            if (!$oModel->hasField('_parent')) {
                ft\Fnc::error(new ft\exception\Model('Для связи типа "---" должно быть задано поле родительской записи (' . $this->getEntityName() . ')'));
            }
        }
    }

    /**
     * Отдает объект модели.
     *
     * @return ft\Model
     */
    public function getModel()
    {
        return $this->oModel;
    }

    /**
     * Отдает тип переменной в базе.
     *
     * @return string
     */
    public function getDatatype()
    {
        return 'int';
    }

    /**
     * Флаг фиктивного поля.
     *
     * @return bool|string
     */
    public function isFictitious()
    {
        if (parent::isFictitious()) {
            parent::isFictitious();
        }

        return $this->getModel()->connectionType();
    }

    /**
     * Отдает флаг "мультиязычное".
     *
     * @return bool
     */
    public function isMultilang()
    {
        return false;
    }
}
