<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 31.05.2017
 * Time: 9:23.
 */

namespace skewer\components\ActiveRecord;

use skewer\base\log\models\Log;

/**
 * Класс прослойка для БД.
 * Нужен чтобы писать в лог CRUD операции с конкретной таблицей
 * Class ActiveRecord.
 */
class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * Флаг писать логи?
     *
     * @var bool
     */
    private static $bWriteLogs = true;

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

    public function afterSave($insert, $changedAttributes)
    {
        $sCurrentTable = static::tableName();

        if ($insert) {
            $sTitle = 'Добавление записи';
        } else {
            $sTitle = 'Изменение записи';
        }

        if ((($insert && static::$bLogCreate) || static::$bLogUpdate) && self::$bWriteLogs) {
            Log::addToLog($sTitle, json_encode($this->getAttributes()), 'DB (' . $sCurrentTable . ')', 4, Log::logUsers);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        if (static::$bLogDelete) {
            $sCurrentTable = static::tableName();

            $sTitle = 'Удаление записи';

            if (self::$bWriteLogs) {
                Log::addToLog($sTitle, json_encode($this->getAttributes()), 'DB (' . $sCurrentTable . ')', 4, Log::logUsers);
            }
        }

        parent::afterDelete();
    }

    public static function enableLogs()
    {
        self::$bWriteLogs = true;
    }

    public static function disableLogs()
    {
        self::$bWriteLogs = false;
    }

    /**
     * Сохранение AR(вставка/обновление).
     *
     * Жизненный цикл сохранения данных:
     * 1. Вызывается [[  initSave() ]]. Если вернет `false`, то процесс сохрания будет прерван
     * 2. Если `$runValidation` = `true` - вызывается [[ beforeValidate() ]] . Если вернет `false`, то процесс сохрания будет прерван
     * 3. Если `$runValidation` = `true` - вызывается [[ validate() ]] . Если вернет `false`, то процесс сохрания будет прерван
     * 4. Если `$runValidation` = `true` - вызывается [[ afterValidate() ]] . Если вернет `false`, то процесс сохрания будет прерван
     * 5. Вызывается [[  beforeSave() ]]. Если вернет `false`, то процесс сохрания будет прерван
     * 6. Выполняется вставка/обновление записи. В случае ошибки процесс сохранения будет прерван.
     * 7. Вызывается [[  afterSave() ]].
     *
     * @param bool $runValidation - запускать валидацию? Если =true будут вызваны методы [[beforeValidate()]], [[validate()]], [[afterValidate()]]
     * @param array $attributeNames - массив имён атрибутов, которые необходимо сохранить
     *
     * @return bool true - в случае успешного сохранения, false - в противном случае
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if (!$this->initSave()) {
            return false;
        }

        if ($this->getIsNewRecord()) {
            return $this->insert($runValidation, $attributeNames);
        }

        return $this->update($runValidation, $attributeNames) !== false;
    }

    /**
     * Метод вызывается перед сохранением записи.
     * Данный метод нужно использовать для установки свойств AR.
     * Для записи регистрации ошибок используйте метод [[ addErrors() ]].
     *
     * @return bool
     */
    public function initSave()
    {
        return !$this->hasErrors();
    }
}
