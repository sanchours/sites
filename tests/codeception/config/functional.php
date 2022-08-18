<?php

$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_TEST_ENTRY_URL;

/**
 * Application configuration for functional tests.
 */
$sFileDbConnection = dirname(__DIR__) . '/_support/db_connect.php';

$aDbConfiguration = [];
if (file_exists($sFileDbConnection)) {
    $aDbConfiguration = json_decode(file_get_contents($sFileDbConnection), true);
}

return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../skewer/config/web.php'),
    require(__DIR__ . '/config.php'),
    $aDbConfiguration,
    [
        'class' => '\skewer\app\Application',
        'components' => [
            'request' => [
                // it's not recommended to run functional tests with CSRF validation enabled
                'enableCsrfValidation' => false,
                // but if you absolutely need it set cookie domain to localhost
                /*
                'csrfCookie' => [
                    'domain' => 'localhost',
                ],
                */
            ],
//            'urlManager' => [
//                'showScriptName' => true,
//            ],
        ],
    ]
);
