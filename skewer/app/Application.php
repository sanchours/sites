<?php

namespace skewer\app;

use skewer\base\site\Site;
use skewer\base\site_module\Request;
use skewer\base\SysVar;
use skewer\base\Twig;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentAdmin;
use skewer\components\i18n;
use yii\debug\Module as DebugModule;
use yii\gii\Module as GiiModule;
use yii\helpers\ArrayHelper;

/** @var bool $YII_DEBUG Указывает работает ли приложение в отладочном режиме */
/** @var string $YII_ENV Указывает в каком окружении приложение работает */
session_start();
if (isset($_SESSION['debugMode4User']) && $_SESSION['debugMode4User']) {
    $YII_DEBUG = true;
    $YII_ENV = 'dev';
}
session_abort();
if (isset($YII_DEBUG)) {
    defined('YII_DEBUG') or define('YII_DEBUG', $YII_DEBUG);
}
if (isset($YII_ENV)) {
    defined('YII_ENV') or define('YII_ENV', $YII_ENV);
}

require_once RELEASEPATH . '/../skewer/app/Yii.php';

require_once 'CacheDropTrait.php';

/**
 * Основной класс приложения в замен стандартному для проброса имен переменных в IDE.
 *
 * @property i18n\I18N $i18n the internationalization application component
 * @property i18n\SectionsPrototype $sections component to a service section
 * @property \skewer\components\config\BuildRegistry $register registry of installed modules and parts
 * @property \skewer\base\site_module\Environment $environment набор переменных среды для передачи между модулями
 * @property \skewer\base\site_module\ProcessList $processList список процессов
 * @property \skewer\base\router\Router $router менеджер для работы с url (временный, потом будет urlManager)
 * @property \skewer\base\site_module\JsonResponse $jsonResponse менеджер для работы с json посылками (временный)
 * @property \skewer\components\seo\Manager $seo менеджер для работы с SEO
 *
 * @method i18n\I18N getI18n Returns the internationalization application component
 */
class Application extends \yii\web\Application
{
    use CacheDropTrait;

    /**
     * {@inheritdoc}
     */
    public $layout = false;

    /**
     * {@inheritdoc}
     */
    public function init() {

        session_set_cookie_params(0, '/', Site::domain());
        \Yii::$app->session->open();

        if (\Yii::$app->session->get('db_test_accept')) {
            \Yii::$app->setComponents(['db' => \Yii::$app->session->get('db_test_accept')]);
        }

        self::defineWebProtocol();

        parent::init();
        $this->setViewPath('@skewer/views');
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest($request)
    {
        mb_internal_encoding('UTF-8');

        /* Регистрация skewer-автолоадера */
        $this->skAutoloaderInit();

        /* Инициализация языка */
        $this->languageInit();

        /* Редиректы */
        $this->redirect();

        /* Инициализация парсеров */
        $this->parserInit();

        /* Инициализация политик */
        $this->authInit();

        /* Инициализация Request */
        $this->skRequestInit();

        $this->setUserIpForDebug();

        return parent::handleRequest($request);
    }

    /**
     * @return i18n\SectionsPrototype $sections component to a service section
     */
    public function getSections()
    {
        return $this->getComponents('section')[0];
    }

    /**
     * Редиректы.
     */
    private function redirect()
    {
        // #stb_note можно взять из вызывавшего метода
        $request = \Yii::$app->getRequest();
        $params = $request->getQueryParams();
        $response = $this->getResponse();

        $sUrl = $request->getUrl();

        $sRequestUri = explode('?', $sUrl)[0];

        // #42836
        // Тут можно было бы использовать \Yii::$app->urlManager->scriptUrl для получения
        // имени скрипта, но площадка часто настроена на выкладку в директорию выше web,
        // из-за чего получаем /web/index.php. Надо либо на уровне инициализации отрезать
        // web папку или здесь убирать её
        // + редирект тут делать не нужно. Это должно быть настроено в конфиге параметром showScriptName
        // !!!! showScriptName - при false убирает имя скрипта при вызове createUrl т.е. редирект нужен

        $sEntryScript = 'index.php';

        if (mb_strpos(ltrim($sUrl, '/'), $sEntryScript) === 0) {
            $iPos = mb_strpos($sUrl, $sEntryScript) + mb_strlen($sEntryScript);
            $sRedirectUrl = mb_substr($sUrl, $iPos);
            $sRedirectUrl = '/' . ltrim($sRedirectUrl, '/');

            \Yii::$app->getResponse()->redirect($sRedirectUrl, '301')->send();
        }

        // для файлов с расширениями для роутера нужно убирать суффикс
        preg_match('/^(.*)\.(.*)$/', $sRequestUri, $return);

        if ($return) {
            \Yii::$app->getUrlManager()->suffix = '';

            return;
        }

        /** @var string $sRawUrl Необработанный урл */
        $sRawUrl = $sRequestUri;

        /** @var string $sCultivatedUrl Обработанный урл */
        $sCultivatedUrl = $sRawUrl . '/';
        $sCultivatedUrl = preg_replace('/\/{2,}/', '/', $sCultivatedUrl);

        if ($sCultivatedUrl !== $sRawUrl) {
            $sRedirectUrl = empty($params) ? $sCultivatedUrl : $sCultivatedUrl . '?' . $request->getQueryString();
            $response->redirect($sRedirectUrl, 301)->send();
            exit;
        }
    }

    /**
     * Отдает значенеие параметра конфигурации по заданному адресу
     * Метод работает по принципу ArrayHelper::getValue.
     *
     * @param array|string $sPath
     * @param null|mixed $sDefault
     *
     * @return mixed
     */
    public function getParam($sPath, $sDefault = null)
    {
        return ArrayHelper::getValue(\Yii::$app->params, $sPath, $sDefault);
    }

    private function skAutoloaderInit()
    {
        require_once RELEASEPATH . 'base/Autoloader.php';
        \skewer\base\Autoloader::init();
    }

    private function languageInit()
    {
        /* Язык по-умолчанию */
        if (SysVar::get('language') !== null) {
            $this->language = SysVar::get('language');
        }
    }

    private function parserInit()
    {
        Twig::Load(
            [],
            \Yii::$app->getParam(['cache', 'rootPath']) . 'Twig/',
            \Yii::$app->getParam(['debug', 'parser']) or YII_DEBUG or YII_ENV_DEV
        );
    }

    private function authInit()
    {
        Auth::init();
    }

    private function skRequestInit()
    {
        Request::init();
    }

    /**
     * Устанавливает ip пользователя sys, если для него включен debug режим в сессии
     */
    private function setUserIpForDebug()
    {
        if (CurrentAdmin::getDebugMode() && CurrentAdmin::isSystemModeByUserData()) {
            foreach (\Yii::$app->getModules(true) as $module) {
                if ($module instanceof GiiModule || $module instanceof DebugModule) {
                    $module->allowedIPs[] = \Yii::$app->getRequest()->getUserIP();
                }
            }
        }
    }

    /**
     * Определяет константу WEBPROTOCOL в зависимости от порта с которого пришли
     * или SysVar enableHTTPS.
     */
    public static function defineWebProtocol()
    {
        // Протокол доступа к сайту: http:// или https://
        if (!defined('WEBPROTOCOL')) {
            $bHttpsPort = (isset($_SERVER['SERVER_PORT']) and ($_SERVER['SERVER_PORT'] == '443'));
            $bEnableHTTPS = (isset(\Yii::$app->components['db']) and \skewer\base\SysVar::get('enableHTTPS'));

            if ($bHttpsPort or $bEnableHTTPS) {
                define('WEBPROTOCOL', 'https://');
            } else {
                define('WEBPROTOCOL', 'http://');
            }
        }
    }
}
