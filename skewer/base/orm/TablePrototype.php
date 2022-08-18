<?php

namespace skewer\base\orm;

use skewer\base\ft;

/**
 * Прототип для класса менеджера модели и сущности
 * Class TablePrototype
 * User: ilya
 * Date: 24.03.14.
 */
class TablePrototype
{
    /** @var string Имя таблицы */
    protected static $sTableName = '';

    /** @var string Имя ключевого поля */
    protected static $sKeyField = 'id';

    /** @var array Описание полей таблицы */
    protected static $aFieldList = [];

    /**
     * Поиск записей.
     *
     * @param null $id
     *
     * @return ActiveRecord|bool|state\StateSelect
     */
    public static function find($id = null)
    {
        if ($id !== null) {
            $oRow = static::getNewRow();
            $res = Query::SelectFrom(static::$sTableName, $oRow)->where(static::$sKeyField, $id)->getOne();
        } else {
            $oRow = static::getNewRow();
            $res = Query::SelectFrom(static::$sTableName, $oRow);
        }

        return $res;
    }

    /**
     * Поиск одной записи по заданному условию.
     *
     * @param array $where Набор условий для секции WHERE
     *
     * @return ActiveRecord|array|bool
     */
    public static function findOne($where)
    {
        return self::find()->where($where)->getOne();
    }

    /**
     * функция создания AR
     * если первичный ключ задан и существует в базе, то будет выбрана эта запись
     * и в нее будут загружены переданные данные.
     *
     * если такой запсиси нет, то будет создан новый AR
     *
     * @param $aData
     *
     * @throws \Exception
     *
     * @return ActiveRecord|bool|state\StateSelect
     */
    public static function load($aData)
    {
        $res = false;

        // если в массиве есть первичный ключ, то у нас update
        if (isset($aData[static::$sKeyField])) {
            $res = static::find($aData[static::$sKeyField]);
        }

        // не нашли, значит инсерт
        if (!$res) {
            $res = static::getNewRow();
        }

        $res->setData($aData);

        return $res;
    }

    /**
     * @param null $id
     *
     * @return state\StateDelete
     */
    public static function delete($id = null)
    {
        if ($id !== null) {
            $res = Query::DeleteFrom(static::$sTableName)->where(static::$sKeyField, $id)->get();
        } else {
            $res = Query::DeleteFrom(static::$sTableName);
        }

        return $res;
    }

    /**
     * @return state\StateUpdate
     */
    public static function update()
    {
        return Query::UpdateFrom(static::$sTableName);
    }

    /**
     * Отдает имя класса AR
     * должен быть ActiveRecord или его наследником
     *
     * @return string
     */
    protected static function getARClass()
    {
        return '\skewer\base\orm\ActiveRecord';
    }

    /**
     * Получить экземлять записи для таблицы.
     *
     * @param array $aData
     *
     * @throws \Exception
     *
     * @return ActiveRecord
     */
    public static function getNewRow($aData = [])
    {
        /** @var ActiveRecord $sARClass */
        $sARClass = static::getARClass();

        if (static::getModel() === null) {
            $oRow = $sARClass::init(static::$sTableName, array_keys(static::$aFieldList), static::$aFieldList);
        } else {
            $oRow = $sARClass::getByFTModel(static::getModel());
        } //init( static::$sTableName, static::getModel()->getAllFieldNames(), array() );

        if (!$oRow instanceof ActiveRecord) {
            throw new \Exception(sprintf('`%s` не является наследником `%s`', $sARClass, 'orm\ActiveRecord'));
        }
        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }

    protected static function initModel()
    {
    }

    public static function getModel()
    {
        if (!ft\Cache::exists(static::$sTableName)) {
            static::initModel();
        }

        return ft\Cache::get(static::$sTableName);
    }

    /**
     * Перестройка таблицы по модели.
     *
     * @return bool
     */
    public static function rebuildTable()
    {
        $oModel = static::getModel();

        if ($oModel === null) {
            return false;
        }

        $oEntity = ft\Entity::get($oModel->getTableName());
        $oEntity->setModel($oModel);
        $oEntity->build();

        return true;
    }

    /**
     * Возвращает имя таблицы.
     *
     * @return string
     */
    public static function getTableName()
    {
        return static::$sTableName;
    }

    /**
     * Returns the fully qualified name of this class.
     *
     * @return string the fully qualified name of this class
     */
    public static function className()
    {
        return get_called_class();
    }
}
