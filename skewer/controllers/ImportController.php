<?php

namespace skewer\controllers;

use skewer\base\site_module\Request;
use skewer\base\SysVar;
use skewer\components\auth\Auth;
use skewer\components\Exchange\ExchangePrototype;
use yii\helpers\StringHelper;
use yii\web\ForbiddenHttpException;

/**
 * Class ImportController - контроллер для общения с системой "1c:Предприятие".
 */
class ImportController extends Prototype
{
    /**
     * @throws ForbiddenHttpException
     */
    public function actionIndex()
    {
        if (!SysVar::get('1c.exchange_active', 0) || !$this->checkAccess()) {
            throw new ForbiddenHttpException('У вас нет доступа к данной странице');
        }
        $oImportManager = ExchangePrototype::getInstance(Request::getStr('type'));
        $oImportManager->executeCommand(Request::getStr('mode'));
    }

    /**
     * Проверка доступа.
     *
     * @return array|bool
     */
    protected function checkAccess()
    {
        if (!$sAutorizationCode = \Yii::$app->request->getHeaders()->get('authorization')) {
            return false;
        }

        $sBasicPart = 'Basic ';
        $sHash = mb_substr($sAutorizationCode, mb_strlen($sBasicPart));
        $sDecodedHash = base64_decode($sHash);

        list($sLogin, $sPassword) = StringHelper::explode($sDecodedHash, ':', false, false);

        return Auth::checkUser('admin', $sLogin, $sPassword);
    }
}
