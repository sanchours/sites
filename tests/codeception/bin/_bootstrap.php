<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', dirname(__DIR__, 3));

require YII_APP_BASE_PATH . '/vendor/autoload.php';
require_once RELEASEPATH . '/../skewer/app/Yii.php';

Yii::setAlias('@tests', dirname(__DIR__, 2));
