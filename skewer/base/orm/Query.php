<?php

namespace skewer\base\orm;

use skewer\base\ft;
use yii\db\Exception;

/**
 * Интерфейс для построения и выполнения запросов к базе данных
 * Class Query.
 */
class Query
{
    /** @var state\StatePrototype Сборщик выбранного типа запроса */
    private $oState;

    /** @var null Сущность для выходных данных */
    private $oEntity;

    public function __construct($mEntity = null)
    {
        if (is_string($mEntity)) {
            //$this->sTableName = $mEntity;
            //$this->sTableAlias = $sAlias ? $sAlias : $mEntity;
            $this->oEntity = ft\Cache::get($mEntity);
            if (!$this->oEntity instanceof ft\Model) {
                return false;
            }
        } elseif (is_object($mEntity) && ($mEntity instanceof ActiveRecord)) {
            $this->oEntity = $mEntity;
        } elseif (is_object($mEntity) && ($mEntity instanceof ft\Model)) {
            $this->oEntity = $mEntity;
        }
        //return false;
    }

    /**
     * ФОрмат на выходные данные.
     *
     * @return ActiveRecord
     */
    public function getEntity()
    {
        return $this->oEntity;
    }

    /**
     * Создать запрос типа SELECT.
     *
     * @return state\StateSelect
     */
    public function select()
    {
        $this->oState = new state\StateSelect($this);

        return $this->oState;
    }

    /**
     * Создать запрос типа UPDATE.
     *
     * @return state\StateUpdate
     */
    public function update()
    {
        $this->oState = new state\StateUpdate($this);

        return $this->oState;
    }

    /**
     * Создать запрос типа DELETE.
     *
     * @return state\StateDelete
     */
    public function delete()
    {
        $this->oState = new state\StateDelete($this);

        return $this->oState;
    }

    /**
     * Создать запрос типа INSERT.
     *
     * @return state\StateInsert
     */
    public function insert()
    {
        $this->oState = new state\StateInsert($this);

        return $this->oState;
    }

    /**
     * Создать запрос типа SELECT для таблицы.
     *
     * @param string $sTableName Имя таблицы
     * @param ActiveRecord|ft\Model|string $oEntity
     *
     * @return state\StateSelect
     */
    public static function SelectFrom($sTableName, $oEntity = null)
    {
        $oQuery = new Query($oEntity);

        return $oQuery->select()->from($sTableName);
    }

    /**
     * Создать запрос типа INSERT для таблицы.
     *
     * @param string $sTableName Имя таблицы
     * @param ActiveRecord $oEntity
     *
     * @return state\StateInsert
     */
    public static function InsertInto($sTableName, $oEntity = null)
    {
        $oQuery = new Query($oEntity);

        return $oQuery->insert()->table($sTableName);
    }

    /**
     * Создать запрос типа UPDATE для таблицы.
     *
     * @param string $sTableName Имя таблицы
     * @param ActiveRecord $oEntity
     *
     * @return state\StateUpdate
     */
    public static function UpdateFrom($sTableName, $oEntity = null)
    {
        $oQuery = new Query($oEntity);

        return $oQuery->update()->from($sTableName);
    }

    /**
     * Создать запрос типа DELETE для таблицы.
     *
     * @param string $sTableName Имя таблицы
     * @param ActiveRecord $oEntity
     *
     * @return state\StateDelete
     */
    public static function DeleteFrom($sTableName, $oEntity = null)
    {
        $oQuery = new Query($oEntity);

        return $oQuery->delete()->from($sTableName);
    }

    /**
     * Выполняет запрос
     *
     * @param string $sQuery Текст запроса
     * @param array|int|string $aData Переменные для запроса
     *
     * @throws Exception
     *
     * @return service\DataBaseAdapter
     */
    public static function SQL($sQuery, $aData = [])
    {
        if (!is_array($aData)) {
            $aData = $aData === null ? [] : [$aData];
        }

        if (func_num_args() > 2) {
            $aParams = func_get_args();
            for ($i = 2; $i < func_num_args(); ++$i) {
                $aData[] = $aParams[$i];
            }
        }

        $oDBAdapter = new service\DataBaseAdapter();
        $oDBAdapter->applyQuery($sQuery, $aData);

        if ($sError = $oDBAdapter->getError()) {
            throw new Exception($sError);
        }

        return $oDBAdapter;
    }

    /**
     * Страрт транзакции.
     */
    public static function startTransaction()
    {
        self::SQL('START TRANSACTION;');
    }

    /**
     * Завершение транзакции.
     */
    public static function commitTransaction()
    {
        self::SQL('COMMIT;');
    }

    /**
     * Откат транзакции.
     */
    public static function rollbackTransaction()
    {
        self::SQL('ROLLBACK;');
    }

    /**
     * Удаление таблицы из базы данных.
     *
     * @param string $table Имя таблицы
     *
     * @throws \Exception
     */
    public static function dropTable($table)
    {
        self::SQL("DROP TABLE IF EXISTS `{$table}`");
//        if ( orm\Connection::errorCode() )
//            throw new \Exception('SQL error: '.orm\Connection::errorInfo(), orm\Connection::errorCode());
    }

    public static function truncateTable($table)
    {
        self::SQL("TRUNCATE TABLE {$table};");
    }
}
