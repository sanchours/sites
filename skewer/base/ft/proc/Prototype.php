<?php

namespace skewer\base\ft\proc;

use skewer\base\ft;
use skewer\base\orm;

/**
 * Прототип процессоров полей
 * Class Prototype.
 */
class Prototype
{
    /** @var ft\Model описание модели */
    protected $oModel;

    /** @var ft\model\Field описание поля */
    protected $oField;

    /** @var orm\ActiveRecord поле сущности */
    protected $oRow;

    /** @var array набор параметров */
    protected $aParamList = [];

    /**
     * @param ft\Model $oModel
     */
    public function setModel($oModel)
    {
        $this->oModel = $oModel;
    }

    /**
     * @param ft\model\Field $oField
     */
    public function setField($oField)
    {
        $this->oField = $oField;
    }

    /**
     * @param orm\ActiveRecord $oRow
     */
    public function setRow($oRow)
    {
        $this->oRow = $oRow;
    }

    /**
     * Отдает текущее значение для поля.
     *
     * @return mixed
     */
    public function getValue()
    {
        $sFieldName = $this->oField->getName();

        return $this->oRow->{$sFieldName};
    }

    /**
     * Задает набор параметров для валидатора.
     *
     * @param $aParamList
     */
    public function setParamList($aParamList)
    {
        $this->aParamList = $aParamList;
    }

    /**
     * Отдает параметр по имени.
     *
     * @param string $sName имя параметра
     * @param null $mDefault то, что будет оддано в случае отсутствия параметра
     *
     * @return null|mixed
     */
    public function getParam($sName, $mDefault = null)
    {
        return isset($this->aParamList[$sName]) ? $this->aParamList[$sName] : $mDefault;
    }

    /**
     * ! системная функция
     * Проверяет правильность заполнения параметров и валидатора
     * Если есть ошибки - может выбросить исключение
     * Должна вызываться после передачи.
     *
     * @throws ft\exception\Model
     */
    public function checkInit()
    {
    }
}
