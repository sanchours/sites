<?php

namespace skewer\base\ft;

use Exception;
use skewer\base\orm\MagicTable;
use skewer\components\catalog;

/**
 * Класс для работы с кэшем ft
 * ver 2.00.
 */
class Cache
{
    /** @var array[] Список ft-моделей в виде массивов */
    private static $aModelList = [];

    /**
     *  @var array[] Массив соответствий идентификаторов модели и их названий
     *  Нужен чтобы запрашивать из кеша ft-модели как по названию так и по их идентификатору
     */
    private static $aIdToNameModel = [];

    /**
     * Проверяет существует ли сущность.
     *
     * @static
     *
     * @param string $sEntityName имя сущности
     *
     * @return bool
     */
    public static function exists($sEntityName)
    {
        return isset(self::$aModelList[$sEntityName]);
    }

    /**
     * Отдает объект-описание сущности.
     *
     * @static
     *
     * @param int|string $mEntityName имя или ид сущности
     *
     * @throws Exception
     *
     * @return Model
     */
    public static function get($mEntityName)
    {
        if (is_numeric($mEntityName) && isset(self::$aIdToNameModel[$mEntityName])) {
            $mEntityName = self::$aIdToNameModel[$mEntityName];
        }

        if (!isset(self::$aModelList[$mEntityName])) {
            // запросник на сущность

            /* @var catalog\model\EntityRow $oEntityRow */
            if (is_numeric($mEntityName)) {
                $oEntityRow = catalog\model\EntityTable::find($mEntityName);
            } else {
                $sValue = $mEntityName;
                if (mb_strpos($mEntityName, 'c_') === 0) {
                    $sValue = mb_substr($mEntityName, 2);
                }

                $oEntityRow = catalog\model\EntityTable::find()
                    ->where('name', $sValue)
                    ->getOne();
            }

            if (empty($oEntityRow)) {
                throw new Exception("Не найдена модель для сущности [{$mEntityName}]");
            }
            self::$aIdToNameModel[$oEntityRow->id] = $oEntityRow->name;

            $oModel = $oEntityRow->getModelFromCache();

            self::set($oEntityRow->name, $oModel);

            return clone $oModel;
        }

        $oConverter = new converter\Arr();

        return $oConverter->dataToFtModel(self::$aModelList[$mEntityName]);
    }

    /**
     * Записать в кэш описание модели.
     *
     * @static
     *
     * @param $sEntityName
     * @param Model $oModel
     */
    public static function set($sEntityName, Model $oModel)
    {
        $oConverter = new converter\Arr();

        self::$aModelList[$sEntityName] = $oConverter->ftModelToData($oModel);
    }

    /**
     * Загрузка системный сущностей.
     *
     * @param $sEntityName
     * @param $sNameSpace
     */
    public static function loadSystemEntity($sEntityName, $sNameSpace)
    {
        self::set($sEntityName, self::get($sNameSpace . '\\' . $sEntityName));
    }

    /**
     * Получение класса для работы с сущностью.
     *
     * @param string $sModelName Имя сущности
     *
     * @return \skewer\base\orm\MagicTable
     */
    public static function getMagicTable($sModelName)
    {
        $oModel = self::get($sModelName);

        $oTable = MagicTable::init($oModel);

        return $oTable;
    }

    /**
     * Очищает внетренний кэш моделей.
     */
    public static function clearCache()
    {
        self::$aModelList = [];
        self::$aIdToNameModel = [];
    }
}
