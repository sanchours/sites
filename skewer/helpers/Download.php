<?php

namespace skewer\helpers;

use yii\web\NotFoundHttpException;

/**
 * Класс для обработки процесса скачивания файлов через php.
 */
class Download
{
    /**
     * Через заголовки отдает содержимое файла.
     *
     * @param string $sFilePath путь до файла
     * @param string $sFileName имя файла после скачивания
     *
     * @throws NotFoundHttpException
     */
    public static function protectedFile($sFilePath, $sFileName = null)
    {
        // проверка наличия
        if (!is_file($sFilePath)) {
            throw new NotFoundHttpException('file not found');
        }
        // размер файла
        $iFileSize = filesize($sFilePath);

        // имя файла
        if (!$sFileName) {
            $sFileName = basename($sFilePath);
        }

        // отдать набор заголовков
        header('HTTP/1.1 200 OK');
        //header ("Date: " . getGMTDateTime ());
        header('X-Powered-By: PHP/' . PHP_VERSION);
        header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
        header('Cache-Control: None');
        header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        header('Content-Disposition: inline; filename="' . $sFileName . '"');
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . $iFileSize);
        header('Age: 0');
        header('Proxy-Connection: close');

        // передать сам файл
        readfile($sFilePath);
    }
}
