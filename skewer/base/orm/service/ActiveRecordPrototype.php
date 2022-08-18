<?php

namespace skewer\base\orm\service;

use skewer\base\ft;

abstract class ActiveRecordPrototype
{
    private $sTableName = '';
    private $sKeyField = 'id';
    private $aFieldDesc = [];
    private $sError = '';

    /** @var string[] Список ошибок */
    private $aErrorList = [];

    /** @var bool флаг наличия изменений при выполнений в базе последней операции */
    private $bUpdated = false;

    /** @var ft\Model Описание сущности */
    private $oModel;

    /**
     * Добавление нового поля.
     *
     * @param $sFieldName
     * @param array $aDesc
     */
    protected function addField($sFieldName, $aDesc = [])
    {
        $sDefValue = $aDesc['default'] ?? '';

        $this->{$sFieldName} = $sDefValue;
        $this->aFieldDesc[$sFieldName] = $aDesc;
    }

    protected function setModel($oModel)
    {
        $this->oModel = $oModel;
    }

    public function getModel()
    {
        return $this->oModel;
    }

    protected function setTableName($sTableName)
    {
        $this->sTableName = $sTableName;
    }

    public function getTableName()
    {
        return $this->sTableName;
    }

    protected function setPrimaryKey($mPKey)
    {
        $this->sKeyField = $mPKey;
    }

    public function primaryKey()
    {
        return $this->sKeyField;
    }

    public function getPrimaryKeyValue()
    {
        $sPK = $this->sKeyField;

        return $this->{$sPK} ?? 0;
    }

    public function setPrimaryKeyValue($sValue)
    {
        $sPK = $this->sKeyField;
        $this->{$sPK} = $sValue;
    }

    /**
     * true, если первичный ключ - составной.
     *
     * @return bool
     */
    public function compositePK()
    {
        return is_array($this->primaryKey());
    }

    /**
     * Сообщает является ли заданное поле ключевым (в составном ключе или одиночном).
     *
     * @param string $sFieldName
     *
     * @return bool
     */
    public function fieldInPK($sFieldName)
    {
        if ($this->compositePK()) {
            return in_array($sFieldName, $this->primaryKey());
        }

        return $this->primaryKey() == $sFieldName;
    }

    public function getFieldDesc()
    {
        return $this->aFieldDesc;
    }

    public function addError($sMsg = '')
    {
        $this->sError = $sMsg;
    }

    public function getError()
    {
        return $this->sError;
    }

    /*
     * Секция работы с ошибками
     */

    /**
     * Отдает список ошибок.
     *
     * @return array
     */
    public function getErrorList()
    {
        return $this->aErrorList;
    }

    /**
     * Отдает флаг наличия ошибки.
     *
     * @return bool
     */
    public function hasError()
    {
        return (bool) $this->aErrorList;
    }

    /**
     * Задает список ошибок.
     *
     * @param array $aErrorList список ошибок
     */
    protected function setErrorList($aErrorList)
    {
        $this->aErrorList = $aErrorList;
    }

    /**
     * Задает ошибку для поля.
     *
     * @param string $sFieldName имя поля
     * @param string $sErrorText текст ошибки
     */
    public function setFieldError($sFieldName, $sErrorText)
    {
        $this->aErrorList[$sFieldName] = $sErrorText;
    }

    /**
     * Очищает список ошибок.
     */
    protected function clearErrors()
    {
        $this->aErrorList = [];
    }

    /**
     * Задание флага того, что данные были модифицированы последним запросом
     *
     * @param mixed $mVal
     *
     * @return bool
     */
    protected function setWasUpdated($mVal)
    {
        $this->bUpdated = (bool) $mVal;
    }

    /**
     * Флаг того, что данные были модифицированы последним запросом
     *
     * @return bool
     */
    public function wasUpdated()
    {
        return $this->bUpdated;
    }

    /**
     * Актуализация полей со сложными связями.
     */
    protected function saveLinkedFields()
    {
        if (!$this->oModel) {
            return false;
        }

        $aFields = $this->oModel->getFileds();
        foreach ($aFields as $oField) {
            $aRel = $oField->getRelationList();
            if (count($aRel)) {
                foreach ($aRel as $oRel) {
                    if ($oRel->getType() == ft\Relation::MANY_TO_MANY) {
                        $sField = $oField->getName();

                        if (!is_array($this->{$sField})) {
                            $this->{$sField} = explode(',', $this->{$sField});
                        }

                        $oField->updLinkRow($this->getPrimaryKeyValue(), $this->{$sField});
                    }
                }
            }
        }

        return true;
    }
}
