<?php

namespace skewer\components\garbage;

use yii\helpers\FileHelper;

/**
 * Class Garbage.
 */
class Garbage
{
    /**@const string Формат именнования директорий */
    const DIR_FORMAT = 'Y-m-d-H-i-s';

    /**
     * Метод переместит файл/директорию $sFilePath в директорию хранения "файлов на удаление",
     * при этом перемещаемой директории будет дано название по времени $mTime
     * Если такая директория существует, она НЕ будет перезаписана.
     *
     * @param $sFilePath - перемещаемый файл/директория
     * @param string $mTime - время её помещения в директорию хранения "файлов на удаление"
     *
     * @return bool - true в случае успешного удаления, false - в противном случае
     */
    public static function moveToGarbageDir($sFilePath, $mTime = 'now')
    {
        if (!file_exists($sFilePath)) {
            return false;
        }

        if ($mTime === 'now') {
            $mTime = time();
        }

        $sGarbageDir = self::getDir();

        if (!file_exists($sGarbageDir)) {
            FileHelper::createDirectory($sGarbageDir);
        }

        $sNewFilePath = $sNewDirPath = $sGarbageDir . date(self::DIR_FORMAT, $mTime);

        if (is_file($sFilePath)) {
            FileHelper::createDirectory($sNewDirPath);
            $sNewFilePath .= \DIRECTORY_SEPARATOR . basename($sFilePath);
        }

        @rename($sFilePath, $sNewFilePath);

        return $sNewFilePath;
    }

    /**
     * Копирует файл/директорию в директорию хранения "файлов на удаление".
     *
     * @param $sFilePath - перемещаемый файл/директория
     * @param string $mTime - время её помещения в директорию хранения "файлов на удаление"
     *
     * @return bool|string
     */
    public static function copyToGarbageDir($sFilePath, $mTime = 'now')
    {
        if (!file_exists($sFilePath)) {
            return false;
        }

        if ($mTime === 'now') {
            $mTime = time();
        }

        $sGarbageDir = self::getDir();

        if (!file_exists($sGarbageDir)) {
            FileHelper::createDirectory($sGarbageDir);
        }

        $sNewFilePath = $sNewDirPath = $sGarbageDir . date(self::DIR_FORMAT, $mTime);

        if (is_file($sFilePath)) {
            FileHelper::createDirectory($sNewDirPath);
            $sNewFilePath .= \DIRECTORY_SEPARATOR . basename($sFilePath);
            @copy($sFilePath, $sNewFilePath);
        } else {
            FileHelper::copyDirectory($sFilePath, $sNewFilePath);
        }

        return $sNewFilePath;
    }

    /**
     * Вернет директорию хранения "файлов на удаление".
     *
     * @return string
     */
    public static function getDir()
    {
        return PRIVATE_FILEPATH . 'garbage/';
    }
}
