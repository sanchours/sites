<?php

namespace unit;

use skewer\app\Application;
use skewer\base\Twig;
use skewer\components\auth\Auth;
use skewer\components\gateway;
use Yii;

class TestBaseCluster
{
    /** @var array набор доступов */
    public $aAccess;
    protected $sBaseName = '';

    public function initialize($mResult)
    {
        $aDBConf = $mResult;
        $this->aAccess = $aDBConf;
        if (!$aDBConf) {
            throw new gateway\Exception('Gateway answer is empty');
        }

        echo 'Тестовая база [' . $aDBConf['name'] . "] создана\n\n";

        Yii::$app->setComponents([
            'db' => [
                'class' => 'skewer\components\db\Connection',
                'dsn' => 'mysql:host=' . $aDBConf['host'] . ';dbname=' . $aDBConf['name'],
                'username' => $aDBConf['user'],
                'password' => $aDBConf['pass'],
                'charset' => 'utf8',
            ],
        ]);

        // до создания базы не может быть гарантировано определен, поскольку определение зависит от SysVar
        Application::defineWebProtocol();

        Auth::init();

        // инициализация событий
        Yii::$app->register->initEvents();

        Twig::Load(
            [],
            Yii::$app->getParam(['cache', 'rootPath']) . 'Twig/',
            Yii::$app->getParam(['debug', 'parser']) or YII_DEBUG or YII_ENV_DEV
        );
    }

    /**
     * Удаляет тестовую базу после использования.
     */
    public function deleteBase()
    {
        try {
            $oClient = gateway\Api::createClient();

            $oClient->addMethod('HostTools', 'deleteTestBase', [$this->aAccess], [$this, 'onBaseDelete']);

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
            echo "\nТестовая база [" . $this->aAccess['name'] . "] удалена\n\n";
        }
    }
}
