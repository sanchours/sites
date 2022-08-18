<?php

// add unit testing specific bootstrap code here

use skewer\app\Application;
use skewer\base\Twig;
use skewer\components\auth\Auth;
use skewer\components\gateway;
use skewer\helpers\Files;
use unit\TestBaseCluster;

defined('IS_UNIT_TEST') || define('IS_UNIT_TEST', true);
defined('USEDOCKER') || define('USEDOCKER', false);

if (!INCLUSTER and !USEDOCKER) {
    die("Can't create base for test.\n\n");
}

$config = require RELEASEPATH . '/config/console.php';

if (USEDOCKER) {
    $config['components']['db'] = require(ROOTPATH . '/skewer/config/config.db.test.docker.php');
}

new Application($config);

Files::init(FILEPATH, PRIVATE_FILEPATH);

if (USEDOCKER) {
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
} else {
    // Иначе мы в кластерном окружении

    // запросить создание тестовой базы у контроллера кластера
    try {
        $oClient = gateway\Api::createClient();

        require_once 'TestBaseCluster.php';
        $oBootstrap = new TestBaseCluster();

        $oClient->addMethod('HostTools', 'createTestBase', [], [$oBootstrap, 'initialize']);

        if (!$oClient->doRequest()) {
            throw new gateway\Exception($oClient->getError());
        }
    } catch (gateway\Exception $e) {
        die('Test base was not created: ' . $e->getMessage() . "\r\n\r\n");
    }

    register_shutdown_function([$oBootstrap, 'deleteBase']);
}

require_once ROOTPATH . 'tests/codeception/unit/data/TestHelper.php';
