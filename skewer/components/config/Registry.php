<?php

namespace skewer\components\config;

use skewer\base\orm;

/**
 * Класс работы с реестром (в БД).
 */
class Registry
{
    const STORAGE_NAME = 'build';

    /**
     * Возвращает массив данных из хранилища $sStorageName.
     *
     * @static
     *
     * @param string $sStorageName Имя хранилища данных
     *
     * @return array|bool
     */
    public static function getStorage($sStorageName = self::STORAGE_NAME)
    {
        $oResult = orm\Query::SQL(
            'SELECT `data` FROM `registry_storage` WHERE `name`=:name',
            ['name' => $sStorageName]
        );
        $mData = $oResult->getValue('data');

        return !$mData ? false : json_decode($mData, true);
    }

    /**
     * Сохраняет массив данных $aStorageData в хранилище $sStorageName.
     *
     * @static
     *
     * @param array $aStorageData Данные, сохраняемые в хранилище
     * @param string $sStorageName Имя хранилища
     *
     * @return bool|mixed
     */
    public static function saveStorage($aStorageData = [], $sStorageName = self::STORAGE_NAME)
    {
        $sQuery = '
                INSERT INTO
                    `registry_storage`
                SET
                    `name`=:name,
                    `data`=:data_insert
                ON DUPLICATE KEY UPDATE
                    `data`=:data_update;';

        $data = json_encode($aStorageData);

        return orm\Query::SQL(
            $sQuery,
            [
                'name' => $sStorageName,
                'data_insert' => $data,
                'data_update' => $data,
            ]
        )->lastId();
    }

    // func
}// class
