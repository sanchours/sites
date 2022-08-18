<?php

use yii\helpers\ArrayHelper;

$skewerParams = require __DIR__ . '/config.php';

/** @noinspection PhpIncludeInspection */
$config = [
    'id' => 'basic',
    'basePath' => ROOTPATH,
    'version' => ltrim(BUILDNUMBER, '0'),
    'bootstrap' => ['log', 'seo'],
    'timeZone' => 'Europe/Moscow',
    'modules' => [
        'rest' => ['class' => '\skewer\modules\rest\Module']
    ],
    'aliases' => [
        '@app/skewer' => RELEASEPATH,
        '@skewer' => RELEASEPATH,
        '@vendor' => RELEASEPATH . '../vendor/',
        '@bower' => RELEASEPATH . '../vendor/bower-asset/',
        '@tests' => ROOTPATH . '/tests',
    ],
    'controllerNamespace' => 'skewer\controllers',
    'components' => [
        'assetManager' => [
            'hashCallback' => static function ($path) {
                //Пробуем обратиться к методу ассета
                $sPath = str_replace(RELEASEPATH, '', $path);
                $sPath = str_replace('/web', '', $sPath);
                $sPath = str_replace('/', '\\', $sPath);

                $sPath = 'skewer\\' . $sPath . '\Asset';

                if (class_exists($sPath)) {
                    $oAsset = new $sPath();
                    if (method_exists($oAsset, 'getHash')) {
                        return $oAsset->getHash($path);
                    }
                }

                $path = (is_file($path) ? dirname($path) : $path) . filemtime($path);

                return sprintf('%x', crc32($path . Yii::getVersion()));
            },
            'converter' => [
                'class' => 'skewer\components\design\CssConverter',
                'commands' => [
                    //'less' => ['css', 'lessc {from} {to} --no-color'],
                    //'ts' => ['js', 'tsc --out {to} {from}'],
                    'css' => ['css', '{from}'],
                ],
            ],
            'appendTimestamp' => true,
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'I_FPEadMsImj6M7FPaeT5t2WFAb6p6r8',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'baseUrl' => '',
            'csrfCookie' => [
                'httpOnly' => true,
                'secure' => true,
            ],
        ],
        'response' => [
            'charset' => 'UTF-8',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
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
                [
                    'class' => '\skewer\base\log\TaskFileTarget',
                    'levels' => ['info'],
                    'categories' => ['task'],
                    'logFile' => '@app/log/task.log',
                    'logVars' => [],
                    'maxFileSize' => 1024 * 5,
                    'maxLogFiles' => 3,
                ],
            ],
        ],
        'seo' => [
            'class' => 'skewer\components\seo\Manager',
        ],
        'db' => require(ROOTPATH . '/config/config.db.php'),
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
        'sections' => '\skewer\components\i18n\DBSections',
        'register' => 'skewer\components\config\BuildRegistry',
        'environment' => 'skewer\base\site_module\Environment',
        'processList' => 'skewer\base\site_module\ProcessList',
        'router' => 'skewer\base\router\Router',
        'jsonResponse' => 'skewer\base\site_module\JsonResponse',
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enableStrictParsing' => false,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'routeParam' => 'url',
            'suffix' => '/',
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'controller' => [
                    'rest/section',
                    'rest/catalog',
                    'rest/news',
                    'rest/orders',
                    'rest/search',
                ], 'patterns' => [
                    'GET,HEAD {id}' => 'view',
                    'POST' => 'create',
                    'GET,HEAD' => 'index',
                ], 'pluralize' => false],
                'POST,GET rest' => 'rest/prototype/version',
                'POST rest/users/register' => 'rest/users/register',
                'POST,GET rest/users/auth' => 'rest/users/auth',
                'POST,GET rest/users/editprofile' => 'rest/users/editprofile',
                'POST rest/users/recoverypass' => 'rest/users/recoverypass',

                'ajax/ajax.php' => 'ajax/ajax',
                'ajax/captcha.php' => 'ajax/captcha',
                'ajax/uploader.php' => 'ajax/uploader',

                'download' => 'download/index',
                'download/index.php' => 'download/index',

                'cron' => 'cron/index',
                'cron/index.php' => 'cron/index',

                'contentgenerator' => 'contentgenerator/index',
                'contentgenerator/name' => 'contentgenerator/name',

                'tipograf' => 'tipograf/index',

                'design' => 'design/index',
                'design/index.php' => 'design/index',
                'design/reset.php' => 'design/reset',

                'gateway' => 'gateway/index',
                'gateway/index.php' => 'gateway/index',

                'local' => 'local/index',
                'local/index.php' => 'local/index',

                'oldadmin' => 'cms/admin',
                'oldadmin/index.php' => 'cms/admin',

                'admin' => 'cms/newadmin',
                'admin/index.php' => 'cms/newadmin',

                'import' => 'import/index',
                'import/1c_exchange.php' => 'import/index',

                'keepalive.php' => 'cms/keepalive',

                'ajax/robokassa.php' => 'payment/index',
                'ajax/payment.php' => 'payment/index',
                'payment.php' => 'payment/index',

                'sys.php' => 'site/sys',
                'robots.txt' => 'region/show-robots',
                'sitemap.xml' => 'region/show-main-sitemap',
                'sitemap_file' => 'region/show-sitemap-file',

                'test-accept' => 'test-accept/index',
                'test-accept/index.php' => 'test-accept/index',
                'test-accept/mobile' => 'test-accept/mobile',
                'test-accept/mobile.php' => 'test-accept/mobile',

                'docs/rest' => 'docs/rest',
                'docs/swagger' => 'docs/swagger',

                [
                    'pattern' => '<url:private_files[a-zA-Z0-9-/_\W\w]*\/[a-zA-Z0-9-/_\W\w]*>',
                    'route' => 'private-files/index',
                    'suffix' => '/',
                    'normalizer' => false, // disable normalizer for this rule
                ],
                //['route'=>'skewer/index','pattern'=>'<url:[a-zA-Z0-9-/_\W]*>/page/<page:[\d]*>', 'encodeParams'=>false],
                ['route' => 'site/index', 'pattern' => '<url:[a-zA-Z0-9-/_\W\w]*>', 'encodeParams' => false],
                /*
                'gii' => 'gii',

                '<cmd:\w+>/<action:\w+>'=>'<cmd>/<action>',
                '<cmd:\w+>/<action:\w+>/<state:\w+>'=>'<cmd>/<action>/<state>',
                '<cmd:\w+>/<action:\w+>/<state:\w+>/<sid:\w+>'=>'<cmd>/<action>/<state>/<sid>',
                */
                //'catchAll' => ['gii']
            ],
        ],
    ],
    'params' => $skewerParams,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => [
            '192.168.0.*', ],
        'generators' => [
            'page_module' => [
                'class' => 'skewer\generators\page_module\Generator',
                'templates' => [
                    'default' => '@skewer/generators/page_module/default',
                ],
            ],
            'page_ar_module' => [
                'class' => 'skewer\generators\page_ar_module\Generator',
                'templates' => [
                    'default' => '@skewer/generators/page_ar_module/default',
                ],
            ],
            'tool_module' => [
                'class' => 'skewer\generators\tool_module\Generator',
                'templates' => [
                    'default' => '@skewer/generators/tool_module/default',
                ],
            ],
            'adm_module' => [
                'class' => 'skewer\generators\adm_module\Generator',
                'templates' => [
                    'default' => '@skewer/generators/adm_module/default',
                ],
            ],
        ],
    ];

    $config['components']['assetManager']['forceCopy'] = false;

    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => [
            '*',
        ],
    ];
}

/*Кэширование схем таблиц*/
//$config['components']['db']['schemaCacheDuration'] = 3600;
//$config['components']['db']['enableSchemaCache']   = 'cache';

$db = $config['components']['db'];
if (isset($db['db'])) {
    unset($config['components']['db']['db']);

    $config['components']['db'] =
    [
        'class' => 'skewer\components\db\Connection',
        'dsn' => 'mysql:host=' . $db['db']['host'] . ';dbname=' . $db['db']['name'],
        'username' => $db['db']['user'],
        'password' => $db['db']['pass'],
        'charset' => 'utf8',
    ];
}

/** Перекрытия */

/** @noinspection PhpIncludeInspection */
$localConf = require ROOTPATH . '/config/web.php';

$config = ArrayHelper::merge($config, $localConf);

return $config;
