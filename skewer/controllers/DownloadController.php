<?php

namespace skewer\controllers;

use skewer\build\Tool\Subscribe\import\Prototype;

class DownloadController extends \skewer\controllers\Prototype
{
    public function actionIndex()
    {
        $aDeny = ['.', '..', '.htaccess'];

        $sFileHash = \Yii::$app->request->get('file_hash', 0);

        $sMode = \Yii::$app->request->get('mode', 'txt');

        $sClassName = 'skewer\build\Tool\Subscribe\import\Type' . mb_strtoupper($sMode);
        $oProvider = new $sClassName();
        $sMode = $oProvider->getFileExt();

        $sFilePath = ROOTPATH . 'web/' . Prototype::$sFileDir . $sFileHash . $sMode;

        $aDeny[] = $sFileHash . $sMode;

        $aFiles = scandir(ROOTPATH . 'web/' . Prototype::$sFileDir);

        foreach ($aFiles as $file) {
            if (array_search($file, $aDeny) === false) {
                unlink(ROOTPATH . 'web/' . Prototype::$sFileDir . $file);
            }
        }

        if ((file_exists($sFilePath)) and (isset($_SESSION['auth']['admin']))) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=out' . $sMode);
            header('Content-Length: ' . filesize($sFilePath));
            readfile($sFilePath);
        }
    }
}
