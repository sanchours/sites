<?php

namespace skewer\base\site;

use skewer\components\gateway;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с серверными данными.
 */
class Server
{
    /**
     * Отдает true если сервер, на котором работает данный сайт - nginx
     * Пока это только заготовка метода.
     *
     * @return bool
     */
    public static function isNginx()
    {
        if (!isset($_SERVER['SERVER_SOFTWARE'])) {
            return false;
        }

        return mb_strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false;
    }

    /**
     * Отдает true если сервер, на котором работает данный сайт - apache
     * Пока это только заготовка метода.
     *
     * @return bool
     */
    public static function isApache()
    {
        if (!isset($_SERVER['SERVER_SOFTWARE'])) {
            return false;
        }

        return mb_strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false;
    }

    /**
     * Возворащает состояние сервера (продакшн/тест).
     *
     * @throws gateway\Exception
     *
     * @return bool
     */
    public static function isProduction()
    {
        if (!INCLUSTER) {
            return true;
        }

        $oClient = gateway\Api::createClient();

        $bResultStatus = false;

        /* @noinspection PhpUnusedParameterInspection */
        $oClient->addMethod('HostTools', 'isProductionServer', [], static function ($mResult, $mError) use (&$bResultStatus) {
            $bResultStatus = $mResult;
        });

        if (!$oClient->doRequest()) {
            return false;
        }

        return $bResultStatus;
    }

    /**
     * Возращает IP прокси сервера через который делает запрос клиент или IP клиента.
     *
     * @return null|string
     */
    public static function getUserIP()
    {
        return ArrayHelper::getValue($_SERVER, 'HTTP_X_FORWARDED_FOR', \Yii::$app->request->getUserIP());
    }
}
