<?php

namespace skewer\controllers;

use skewer\components\auth\CurrentAdmin;
use skewer\modules\rest\docs\Asset;
use skewer\modules\rest\docs\SwaggerUIAsset;
use yii\web\UnauthorizedHttpException;

/**
 * API Documentation Class.
 */
class DocsController extends Prototype
{
    const PATH_DOCS = 'skewer/modules/rest/docs';

    /**
     * Интерфейст для документации по REST.
     *
     * @throws UnauthorizedHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionRest()
    {
        if (!CurrentAdmin::isAuthorized()) {
            throw new UnauthorizedHttpException();
        }
        SwaggerUIAsset::register(\Yii::$app->view);
        Asset::register(\Yii::$app->view);

        echo \Yii::$app->getView()->renderPhpFile(ROOTPATH . self::PATH_DOCS . '/templates/index.php');
    }

    /**
     * Отдает json файл с описанием протокола.
     *
     * @throws UnauthorizedHttpException
     */
    public function actionSwagger()
    {
        if (!CurrentAdmin::isAuthorized()) {
            throw new UnauthorizedHttpException();
        }
        $sFileContent = file_get_contents(ROOTPATH . self::PATH_DOCS . '/web/json/docs.json');
//        echo \Yii::$app->getView()->renderPhpFile(ROOTPATH.self::PATH_DOCS.'/templates/docs.php',['sFileContent'=>$sFileContent]);
        echo $sFileContent;
    }
}
