<?php

namespace skewer\modules\rest;

use skewer\base\SysVar;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;


class Module extends \yii\base\Module
{
    /** Текущая версия restApi контроллеров */
    const VERSION = '1.0';

    public $controllerNamespace = 'skewer\modules\rest\controllers';


    public function init()
    {
        parent::init();
        \Yii::$app->user->enableSession = false;
    }

    public function beforeAction($action)
    {
        if (!$this->isEnableRest()) {
            throw new NotFoundHttpException();
        }

        if (!$this->checkAccess()) {
            throw new ForbiddenHttpException();
        }

        $this->addVersionToHeader();

        return parent::beforeAction($action);
    }

    /**
     * @return string|null
     */
    private function isEnableRest()
    {
        return SysVar::get('REST.enable');
    }

    /**
     * @return bool
     */
    private function checkAccess()
    {
        $apiKey = SysVar::get('REST.api_key');
        if (!$apiKey) {
            return false;
        }

        $comeApiKey = \Yii::$app->request->getHeaders()->get('api-key');
        return $comeApiKey === $apiKey;
    }

    private function addVersionToHeader()
    {
        header('X_Rest_Api_Version: ' . self::VERSION);
    }
}