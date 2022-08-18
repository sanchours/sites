<?php



//ini_set('display_errors',1);
//ini_set('memory_limit', '512M');
//ini_set("max_execution_time", "300");
// comment out the following two lines when deployed to production
$YII_DEBUG = false;
$YII_ENV = 'prod';

require_once __DIR__ . '/../config/constants.generated.php';

defined('RELEASEPATH') or define('RELEASEPATH', ROOTPATH . 'skewer/');

require_once RELEASEPATH . '/config/constants.php';

require_once RELEASEPATH . '/../vendor/autoload.php';
require_once RELEASEPATH . '/../skewer/app/Application.php';

$config = require RELEASEPATH . '/config/web.php';

$app = (new \skewer\app\Application($config))->run();

exit;