<?php

$skewerParams = require __DIR__ . '/config.php';

return [
    'id' => 'basic-console',
    'basePath' => ROOTPATH,
    'language' => 'ru',
    'timeZone' => 'Europe/Moscow',
    'bootstrap' => ['log', 'seo'],
//    'enableCoreCommands' => false,
    'controllerNamespace' => 'app\skewer\console',
    'controllerMap' => [
        'fixture' => [
            'class' => '\yii\console\controllers\FixtureController',
            'namespace' => 'tests\codeception\fixtures',
        ],
    ],
    'aliases' => [
        '@app/skewer' => RELEASEPATH,
        '@skewer' => RELEASEPATH,
        '@tests' => ROOTPATH . '/tests',
        '@webroot' => WEBPATH,
        '@web' => WEBPATH,
    ],
    'components' => [
        'db' => defined('IS_UNIT_TEST') ? null : require(ROOTPATH . '/config/config.db.php'),
        'cache' => 'yii\caching\FileCache',
        'sections' => '\skewer\components\i18n\DBSections',
        'register' => 'skewer\components\config\BuildRegistry',
        'router' => 'skewer\base\router\Router',
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/log/error.log',
                    'logVars' => [],
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 3,
                ],
                [
                    'class' => '\skewer\base\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['accessLog'],
                    'logFile' => '@app/log/access.log',
                    'logVars' => [],
                    'maxFileSize' => 1024 * 5,
                    'maxLogFiles' => 3,
                ],
            ],
        ],
        'seo' => [
            'class' => 'skewer\components\seo\Manager',
        ],
        'i18n' => [
            'class' => '\skewer\components\i18n\I18N',
            'translations' => [
                'app*' => [
                    'class' => '\skewer\components\i18n\MessageSource',
                    'sourceLanguage' => 'ru',
                    'forceTranslation' => true,
                    'basePath' => '@app/cache/language',
                ],
                '*' => [
                    'class' => '\skewer\components\i18n\MessageSource',
                    'sourceLanguage' => 'ru',
                    'forceTranslation' => true,
                    'basePath' => '@app/cache/language',
                ],
            ],
        ],
    ],
    'params' => $skewerParams,
];
