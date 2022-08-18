<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YII_TEST_ENTRY_URL') or define('YII_TEST_ENTRY_URL', parse_url(\Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_PATH));
defined('YII_TEST_ENTRY_FILE') or define('YII_TEST_ENTRY_FILE', dirname(__DIR__, 2) . '/web/index-test.php');

require_once __DIR__ . '/../../config/constants.generated.php';
defined('RELEASEPATH') or define('RELEASEPATH', ROOTPATH . 'skewer/');

require_once RELEASEPATH . '/config/constants.php';

require_once __DIR__ . '/../../vendor/autoload.php';
require_once RELEASEPATH . '/../skewer/app/Yii.php';

require_once RELEASEPATH . 'base/Autoloader.php';
\skewer\base\Autoloader::init();

$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_TEST_ENTRY_URL;
$_SERVER['SERVER_NAME'] = parse_url(\Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_HOST);
$_SERVER['SERVER_PORT'] = parse_url(\Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_PORT) ?: '80';

Yii::setAlias('@tests', dirname(__DIR__));
