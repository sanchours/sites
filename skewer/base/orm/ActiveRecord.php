<?php

namespace skewer\base\orm;

use skewer\base\ft;
use skewer\base\log\models\Log;
use skewer\base\orm\service\ActiveRecordPrototype;

/**
 * Прототип для сущностей типа ActiveRecord
 * Class ActiveRecord
 * User: ilya
 * Date: 24.03.14.
 */
class ActiveRecord extends ActiveRecordPrototype
{
    /**
     * Флаг писать логи?
     *
     * @var bool
     */
    public static $bWriteLogs = true;

    /**
     * Флаг логировать ли обновление записи.
     *
     * @var bool
     */
    protected static $bLogUpdate = true;

    /**
     * Флаг логированть ли создание записи.
     *
     * @var bool
     */
    protected static $bLogCreate = true;

    /**
     * Флаг логировать ли удаление записи.
     *
     * @var bool
     */
    protected static $bLogDelete = true;

    /**
     * Сохранение состояния записи.
     *
     * Жизненный цикл сохранения данных:
     * 1.Вызывается [[  initSave() ]]. Если вернет `false`, то процесс сохрания будет прерван
     * 2.Выполняется валидация полей
     * 3.Выполняется сохранение записи
     * 4.Выполняется метод [[ afterSave() ]]
     *
     * @return bool
     */
    public function save()
    {
        if (!$this->initSave()) {
            return false;
        }

        $sPKey = $this->primaryKey();

        // запустить валидаторы
        if (!$this->executeValidators()) {
            return false;
        }

        $aData = $this->getData();

        // удаление значений фиктивных полей
        if ($oModel = $this->getModel()) {
            foreach ($oModel->getFileds() as $oField) {
                if ($oField->isFictitious()) {
                    unset($aData[$oField->getName()]);
                }
            }
        }

        $oQuery = Query::InsertInto($this->getTableName());

        foreach ($aData as $sFieldName => $sValue) {
            $oQuery->set($sFieldName, $sValue);
        }

        $oQuery->onDuplicateKeyUpdate();

        foreach ($aData as $sFieldName => $sValue) {
            $oQuery->set($sFieldName, $sValue);
        }

        //echo $oQuery->getQuery();
        $res = $oQuery->get();
        $this->setWasUpdated($res);

        $this->afterSave(!(bool) $aData[$this->primaryKey()], $aData);

        if ($this->compositePK()) {
            // сохранение связанных полей
            $this->saveLinkedFields();

            return true;
        }
        if ($res && (!$this->{$sPKey} || $this->{$sPKey} == 'NULL')) {
            $this->{$sPKey} = $res;
        }

        // сохранение связанных полей
        $this->saveLinkedFields();

        return (int) $this->{$sPKey};
    }

    /**
     * Удаление записи.
     *
     * @return bool
     */
    public function delete()
    {
        if (!$this->beforeDelete()) {
            return false;
        }

        $sPKey = $this->primaryKey();
        if (!isset($this->{$sPKey})) {
            return false;
        }

        $res = Query::DeleteFrom($this->getTableName())->where($sPKey, $this->{$sPKey})->get();

        $this->afterDelete($this);

        return (bool) $res;
    }

    /**
     * Действия, выполняемые до запуска сохранения записи
     * Данный метод нужно использовать для установки свойств AR.
     * Для записи регистрации ошибок используйте метод [[ setFieldError() ]].
     *
     * @return bool Вернет false -  в случае наличия ошибок
     */
    public function initSave()
    {
        $bIsValid = !$this->hasError();

        return $bIsValid;
    }

    /**
     * Действия, выполняемые до удаления записи.
     * При возврате false - удаление будет прервано.
     *
     * @return bool
     */
    public function beforeDelete()
    {
        return true;
    }

    /**
     * Создание новой записи по описанию полей.
     *
     * @param string $sTableName Имя таблицы
     * @param array $aFieldList Список полей для добавления в запись
     * @param array $aFieldDesc Описания полей
     *
     * @return ActiveRecord
     */
    public static function init($sTableName, $aFieldList, $aFieldDesc = [])
    {
        $oRow = new self();

        $oRow->setTableName($sTableName);

        foreach ($aFieldList as $sField) {
            $oRow->addField($sField, $aFieldDesc[$sField] ?? []);
        }

        return $oRow;
    }

    /**
     * Создание новой записи по FT модели.
     *
     * @param ft\Model $oModel
     *
     * @return ActiveRecord
     */
    public static function getByFTModel(ft\Model $oModel)
    {
        $oRow = new static();

        $oRow->setModel($oModel);
        $oRow->setTableName($oModel->getTableName());

        foreach ($oModel->getFileds() as $oField) {
            $oRow->addField($oField->getName(), ['default' => $oField->getDefault()]);
        }

        return $oRow;
    }

    /**
     * Заполнение полей по массивы данных.
     *
     * @param array $aData
     *
     * @return bool
     */
    public function load($aData = [])
    {
        if (empty($aData)) {
            return false;
        }

        foreach ($aData as $sFieldName => $mValue) {
            if (isset($this->{$sFieldName})) {
                $this->{$sFieldName} = $mValue;
            }
        }

        return true;
    }

    /**
     * Задать набор значений.
     *
     * @param array $aData
     */
    public function setData($aData = [])
    {
        if ($this->load($aData)) {
            $this->leadValues();
        }
    }

    /**
     * Получеть набор значений как массив.
     *
     * @param bool $bByModel флаг вывода только полей модели
     *
     * @return array
     */
    public function getData($bByModel = true)
    {
        $aData = [];
        $aFieldList = $this->getFieldDesc();

        if (!empty($aFieldList) && $bByModel) {
            foreach ($aFieldList as $sFieldName => $aFieldDesc) {
                if (isset($this->{$sFieldName})) {
                    $aData[$sFieldName] = $this->{$sFieldName};
                }
            }
        } else {
            foreach ($this as $sFieldName => $aFieldDesc) {
                $aData[$sFieldName] = $this->{$sFieldName};
            }
        }

        return $aData;
    }

    /**
     * Возвращает значение поля.
     *
     * @param string $sFieldName Имя поля
     * @param mixed $mDefValue Значение если не существует поле
     *
     * @return mixed
     */
    public function getVal($sFieldName, $mDefValue = '')
    {
        return $this->{$sFieldName} ?? $mDefValue;
    }

    /**
     * Задает значение для поля.
     *
     * @param string $sFieldName Имя поля
     * @param mixed $mValue Значение поля
     * @param bool $bOnlyExist
     */
    public function setVal($sFieldName, $mValue, $bOnlyExist = true)
    {
        if (isset($this->{$sFieldName}) || !$bOnlyExist) {
            $this->{$sFieldName} = $mValue;
        }
    }

    /**
     * Применяет набор валидаторов к заданному набору данных.
     *
     * @return bool
     */
    protected function executeValidators()
    {
        $oModel = $this->getModel();

        if ($oModel === null) {
            return true;
        }

        // валидация
        $bIsValid = true;
        $aErrorList = [];

        foreach ($oModel->getFileds() as $oField) {
            if (!$oField->getAttr('active')) {
                continue;
            }

            foreach ($oField->getValidatorList($this) as $oValidator) {
                if (!$oValidator->isValid()) {
                    $bIsValid = false;
                    $aErrorList[$oField->getName()] = $oValidator->getErrorText();
                    break;
                }
            }
        }

        // очистка списка ошибок
        $this->clearErrors();

        // если ошибки есть
        if (!$bIsValid) {
            $this->setErrorList($aErrorList);

            return false;
        }

        return true;
    }

    /**
     * Перевод объекта в строку.
     *
     * @return string
     */
    public function __toString()
    {
        $sKeyName = $this->primaryKey();

        return (string) $this->{$sKeyName};
    }

    /**
     * Выполняет приводит к нужному типу и проверку данных.
     *
     * @return bool
     */
    private function leadValues()
    {
        $oModel = $this->getModel();

        if ($oModel === null) {
            return true;
        }

        foreach ($oModel->getFileds() as $sFieldName => $oField) {
            if ($oField->isFictitious()) {
                continue;
            }

            $this->{$sFieldName} = $this->getValidValue($this->{$sFieldName}, $oField);
        }

        return true;
    }

    /**
     * Отдает валидное значение для заданного поля.
     *
     * @param mixed $mValue тукущее значение поля
     * @param ft\model\Field $oField описание поля
     *
     * @return mixed
     */
    private function getValidValue($mValue, ft\model\Field $oField)
    {
        switch ($oField->getDatatype()) {
            case 'int':
                $mValue = (int) $mValue;
                break;
            case 'bool':
                $mValue = (bool) $mValue;
                break;
            case 'float':
                $mValue = (float) $mValue;
                break;
        }

        return $mValue;
    }

    public function afterSave($insert, $aData)
    {
        $sCurrentTable = $this->getTableName();

        if ($insert) {
            $sTitle = 'Добавление записи';
        } else {
            $sTitle = 'Изменение записи';
        }

        if ((($insert && static::$bLogCreate) || static::$bLogUpdate) && self::$bWriteLogs) {
            Log::addToLog($sTitle, json_encode($aData), 'DB (' . $sCurrentTable . ')', 4, Log::logUsers);
        }
    }

    /**
     * Действия, выполняемые после удаления записи.
     *
     * @param self $oRow - удалённая строка
     */
    public function afterDelete($oRow)
    {
        if (static::$bLogDelete) {
            $sCurrentTable = $this->getTableName();

            $sTitle = 'Удаление записи';

            if (self::$bWriteLogs) {
                Log::addToLog($sTitle, json_encode($oRow), 'DB (' . $sCurrentTable . ')', 4, Log::logUsers);
            }
        }
    }

    public static function enableLogs()
    {
        self::$bWriteLogs = true;
    }

    public static function disableLogs()
    {
        self::$bWriteLogs = false;
    }
}
