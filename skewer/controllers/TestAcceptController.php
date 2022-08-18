<?php

namespace skewer\controllers;

use skewer\base\log\models\Log;
use skewer\base\site\Site;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentAdmin;
use skewer\components\auth\models\Users;
use skewer\components\gateway;
use tests\acceptanceKS\mobile\Api;

/**
 * Класс для приемочного тестирования.
 */
class TestAcceptController extends Prototype
{
    public function actionIndex()
    {
        if (YII_ENV_DEV && $this->checkAccess() && !\Yii::$app->session->get('db_test_accept')) {
            \Yii::$app->session->destroy();
            $this->actionCreateTestDB();
        } elseif (YII_ENV_DEV && $this->checkAccess() && \Yii::$app->session->get('db_test_accept')) {
            $this->actionDeleteTestDB();
        }
    }

    private function actionCreateTestDB()
    {
        try {
            $oClient = gateway\Api::createClient();

            $this->setLogData('Запуск приемочных тестов');

            $oClient->addMethod('HostTools', 'createTestBase', [], [$this, 'initialize']);

            if (!$oClient->doRequest()) {
                throw new gateway\Exception($oClient->getError());
            }
        } catch (gateway\Exception $e) {
            $sMess = 'Test base was not created: ' . $e->getMessage() . "\r\n\r\n";
            $this->setLogData($sMess);
            die($sMess);
        }
    }

    private function setLogData($sDescription)
    {
        if (!isset($_SESSION['auth'][CurrentAdmin::$sLayer]['userData']['login'])) {
            $_SESSION['auth'][CurrentAdmin::$sLayer]['userData']['login'] = 'tester';
        }

        Log::addToLog('Приемочное тестирование', $sDescription, 'Acceptance test', 4, Log::logSystem);
        \Yii::$app->session->destroy();
    }

    protected function checkAccess()
    {
        global $config;

        $allowedIPs = (isset($config['modules']['debug']['allowedIPs'])) ? $config['modules']['debug']['allowedIPs'] : [];
        $ip = \Yii::$app->getRequest()->getUserIP();
        foreach ($allowedIPs as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = mb_strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                return true;
            }
        }
        foreach ($allowedIPs as $hostname) {
            $filter = gethostbyname($hostname);
            if ($filter === $ip) {
                return true;
            }
        }
        $this->setLogData('Попытка запуска приемочного тестирования без прав доступа');
        die('You are not allowed to access!');
    }

    public function initialize($mResult)
    {
        $aDBConf = $mResult;

        if (!$aDBConf) {
            throw new gateway\Exception('Gateway answer is empty');
        }
        \Yii::$app->session->set('db_test_accept', [
            'class' => 'skewer\components\db\Connection',
            'dsn' => 'mysql:host=' . $aDBConf['host'] . ';dbname=' . $aDBConf['name'],
            'username' => $aDBConf['user'],
            'password' => $aDBConf['pass'],
            'charset' => 'utf8',
        ]);

        //необходимый параметр для последующего удаления базы
        \Yii::$app->session->set('db_test_config', $mResult);

        if (!$this->setSysUserPass()) {
            die('Не был создан системный пользователь!');
        }

        \Yii::$app->response->redirect(Site::httpDomain());
    }

    private function setSysUserPass()
    {
        if (\Yii::$app->session->get('db_test_accept')) {
            /** @var Users $user */
            $user = Users::findOne(['login' => 'sys']);
            $sPass = '123123';
            $user->pass = Auth::buildPassword($user->login, $sPass);

            return $user->save();
        }

        return false;
    }

    private function actionDeleteTestDB()
    {
        $aAccess = \Yii::$app->session->get('db_test_config');
        try {
            $oClient = gateway\Api::createClient();
            $oClient->addMethod('HostTools', 'deleteTestBase', [$aAccess], [$this, 'onBaseDelete']);

            if (!$oClient->doRequest()) {
                throw new gateway\Exception($oClient->getError());
            }
        } catch (gateway\Exception $e) {
            die('Test base was not deleted: ' . $e->getMessage() . "\r\n\r\n");
        }
    }

    /**
     * Callback при удалении тестовых доступов SMS'кой.
     *
     * @param $mResult
     * @param $mError
     */
    public function onBaseDelete($mResult, $mError)
    {
        if (!$mResult) {
            echo "Test base was not deleted: {$mError}";
        } else {
            \Yii::$app->session->destroy();
            echo "\nБаза для приемочного тестирования была удалена";
        }
    }

    public function actionMobile()
    {
        if (!Api::createConfigAndSections()) {
            $sMess = 'Не были созданы конфигурационные файлы для мобильного приложения!';
            $this->setLogData($sMess);
            die($sMess);
        }
        \Yii::$app->response->redirect(Site::httpDomain());
    }
}
