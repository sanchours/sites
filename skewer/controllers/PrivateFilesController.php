<?php
/**
 * Контроллер для отдачи скрытых файлов (private_files).
 */

namespace skewer\controllers;

use skewer\components\auth\CurrentAdmin;

class PrivateFilesController extends \skewer\controllers\Prototype
{
    public function actionIndex()
    {
        if ((bool) CurrentAdmin::canDo('skewer\\build\\Tool\\Policy\\Module', 'canPrivateFiles') || CurrentAdmin::isSystemMode()) {
            //этот пользователь может использовать private_files.
            $aPaths = explode('/', \Yii::$app->request->getUrl());
            $aPaths = array_reverse($aPaths);

            //преобразуем URL в путь и имя файла
            $aPaths = array_slice($aPaths, 1, count($aPaths) - 3);

            $sFileName = $aPaths[1] . '.' . $aPaths[0];

            unset($aPaths[1], $aPaths[0]);

            $aPaths = array_reverse($aPaths);
            $sFileFullPath = PRIVATE_FILEPATH . \DIRECTORY_SEPARATOR . implode('/', $aPaths) . '/' . $sFileName;

            if (file_exists($sFileFullPath)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . $sFileName);
                header('Content-Length: ' . filesize($sFileFullPath));
                readfile($sFileFullPath);
                exit;
            }
        }

        exit('File not found!');
    }
}
