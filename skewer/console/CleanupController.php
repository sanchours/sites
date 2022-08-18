<?php

namespace app\skewer\console;

//require_once (RELEASEPATH . 'libs/simple_html_dom/simple_html_dom.php');

class CleanupController extends Prototype
{
    const FILES_PHOTO = 'files';

    /**
     * @throws \yii\db\Exception
     */
    public function actionCleanup()
    {
        $cleanupObject = new \skewer\components\cleanup\CleanupController();

        $cleanupObject->init();
        $cleanupObject->execute();
        $cleanupObject->complete();
    }
}
