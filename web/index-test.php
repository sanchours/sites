<?php

// NOTE: Make sure this file is not accessible when deployed to production
if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', '192.168.0.143', '192.168.0.195'])) {
    die('You are not allowed to access this file.');
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require_once __DIR__ . '/../config/constants.generated.php';

defined('RELEASEPATH') or define('RELEASEPATH', ROOTPATH . 'skewer/');

require_once RELEASEPATH . '/config/constants.php';

require_once RELEASEPATH . '/../vendor/autoload.php';
require_once RELEASEPATH . '/../skewer/app/Yii.php';
require_once RELEASEPATH . '/../skewer/app/Application.php';

$config = require RELEASEPATH . '/config/web.php';

$app = (new \skewer\app\Application($config))->run();

exit;
