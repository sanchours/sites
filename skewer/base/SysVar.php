<?php

namespace skewer\base;

use skewer\base\orm\Query;

/**
 * Класс для работы с системными переменными.
 */
class SysVar
{
    /**
     * @var string Имя текущей таблицы, с которой работает маппер
     */
    protected static $sCurrentTable = 'sys_vars';

    private static $cache = null;

    /**
     * возвращает значение системной переменной.
     *
     * @static
     *
     * @param string $sName
     * @param string $mDef Значение по умолчанию
     *
     * @return null|string
     */
    public static function get($sName, $mDef = null)
    {
        if (!$sName or !is_string($sName)) {
            return;
        }

        $sTable = self::$sCurrentTable;

        if (self::$cache === null) {
            self::$cache = \yii\helpers\ArrayHelper::map(\Yii::$app->db->createCommand("SELECT * FROM `{$sTable}` WHERE 1;")->queryAll(), 'sv_name', 'sv_value');
        }

        return \yii\helpers\ArrayHelper::getValue(self::$cache, $sName, $mDef);
    }

    /**
     * Если SysVar не задан, устанавливаем в дефолтное значение.
     *
     * @param $sName
     * @param string $sDefault
     *
     * @return null|string
     */
    public static function getSafe($sName, $sDefault = '')
    {
        $result = self::get($sName);
        if ($result === null) {
            $bRes = self::set($sName, $sDefault);

            return $bRes ? $sDefault : null;
        }

        return $result;
    }

    /**
     * сохранение значения системной переменной.
     *
     * @static
     *
     * @param string $sName название переменной
     * @param mixed $mValue значение переменной
     *
     * @return bool
     */
    public static function set($sName, $mValue)
    {
        if (!$sName or !is_string($sName)) {
            return false;
        }

        $sTable = self::$sCurrentTable;

        $oResult = Query::SQL(
            "INSERT INTO `{$sTable}` (sv_name, sv_value)
                    VALUES (:name, :value)
                    ON DUPLICATE KEY UPDATE sv_value=:value_update",
            [
                'name' => $sName,
                'value' => $mValue,
                'value_update' => $mValue,
            ]
        );

        if (self::$cache !== null) {
            self::$cache[$sName] = $mValue;
        }

        return (bool) $oResult->affectedRows();
    }

    /**
     * Удаляет значение из хранилища.
     *
     * @param $sName
     *
     * @return bool
     */
    public static function del($sName)
    {
        $sTable = self::$sCurrentTable;

        $bRes = (bool) \Yii::$app->db->createCommand(
            "DELETE FROM `{$sTable}` WHERE `sv_name`=:val;",
            ['val' => $sName]
        )->query()->count();

        if ($bRes and isset(self::$cache[$sName])) {
            unset(self::$cache[$sName]);
        }

        return $bRes;
    }

    /** Сбросить кеш */
    public static function clearCache()
    {
        self::$cache = null;
    }
}
