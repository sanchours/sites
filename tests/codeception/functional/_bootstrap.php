<?php

// add unit testing specific bootstrap code here

use skewer\base\log\models\Log;
use skewer\base\Twig;
use skewer\components\gateway;
use Symfony\Component\Console\Output\ConsoleOutput;

defined('YII_ENV') or define('YII_ENV', 'test');
defined('IS_UNIT_TEST') || define('IS_UNIT_TEST', true);

if (!INCLUSTER) {
    die("Site is not in cluster! Can't create base for test.");
}

$config = require dirname(__DIR__) . '/config/functional.php';
//$config = require(RELEASEPATH.'/config/console.php');
unset($config['class']);
new \skewer\app\Application($config);

\skewer\helpers\Files::init(FILEPATH, PRIVATE_FILEPATH);

// запросить создание тестовой базы у контроллера кластера
try {
    $oClient = gateway\Api::createClient();

    $oBootstrap = new TestBootStrapFunctional();

    $oClient->addMethod('HostTools', 'createTestBase', [], [$oBootstrap, 'initialize']);

    if (!$oClient->doRequest()) {
        throw new gateway\Exception($oClient->getError());
    }
} catch (gateway\Exception $e) {
    die('Test base was not created: ' . $e->getMessage() . "\r\n\r\n");
}

class TestBootStrapFunctional
{
    protected $sBaseName = '';

    /** @var array набор доступов */
    public $aAccess;

    public function initialize($mResult)
    {
        $aDBConf = $mResult;
        $this->aAccess = $aDBConf;
        if (!$aDBConf) {
            throw new gateway\Exception('Gateway answer is empty');
        }
        $oConsole = new ConsoleOutput();
        $oConsole->writeln('Тестовая база (functional) [' . $aDBConf['name'] . '] создана');

        $aDbComponent = [
            'class' => 'skewer\components\db\Connection',
            'dsn' => 'mysql:host=' . $aDBConf['host'] . ';dbname=' . $aDBConf['name'],
            'username' => $aDBConf['user'],
            'password' => $aDBConf['pass'],
            'charset' => 'utf8',
        ];

        // Записываем данные о подключении во временный файл
        file_put_contents(
            dirname(__DIR__) . '/_support/db_connect.php',
            json_encode([
                    'components' => ['db' => $aDbComponent],
                ])
        );

        \Yii::$app->setComponents($aDbComponent);

        // до создания базы не может быть гарантировано определен, поскольку определение зависит от SysVar
        \skewer\app\Application::defineWebProtocol();

        Log::disableLogs();

        // инициализация событий
        \Yii::$app->register->initEvents();

        Twig::Load(
            [],
            \Yii::$app->getParam(['cache', 'rootPath']) . 'Twig/',
            \Yii::$app->getParam(['debug', 'parser']) or YII_DEBUG or YII_ENV_DEV
        );
    }

    /**
     * Удаляет тестовую базу после использования.
     *
     * @throws gateway\Exception
     */
    public function deleteBase()
    {
        try {
            unlink(dirname(__DIR__) . '/_support/db_connect.php');

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
            echo "\nТестовая база (functional) [" . $this->aAccess['name'] . "] удалена\n\n";
        }
    }
}

register_shutdown_function([$oBootstrap, 'deleteBase']);
