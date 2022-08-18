<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 02.07.2014
 * Time: 14:12.
 */

namespace skewer\base\site;

use skewer\base\SysVar;
use skewer\components\config\installer;
use skewer\components\gateway;
use yii\base\UserException;

class Type
{
    /** тип сайта "Информационный" */
    const info = 'info';

    /** тип сайта "Каталог" */
    const catalog = 'catalog';

    /** тип сайта "Интернет магазин" */
    const shop = 'shop';

    /**
     * Отдает тип версии сайта.
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function getAlias()
    {
        $sType = SysVar::get('site_type');

        if (!$sType) {
            throw new \Exception('Site type is not setted');
        }

        return $sType;
    }

    /**
     * Сообщает является ли сайт "Информационным".
     *
     * @return bool
     */
    public static function isInfo()
    {
        return self::getAlias() === self::info;
    }

    /**
     * Проверка наличия установленного модуля каталога.
     *
     * @return bool
     */
    public static function hasCatalogModule()
    {
        return !self::isInfo();
    }

    /**
     * Проверка наличия установленного модуля коллекций.
     *
     * @return bool
     */
    public static function hasCollectionModule()
    {
        $oInstaller = new installer\Api();

        return $oInstaller->isInstalled('Collections', Layer::CATALOG);
    }

    /**
     * Сообщает является ли сайт "Каталог".
     *
     * @return bool
     */
    public static function isCatalog()
    {
        return self::getAlias() === self::catalog;
    }

    /**
     * Сообщает является ли сайт "Интернет магазин".
     *
     * @return bool
     */
    public static function isShop()
    {
        return self::getAlias() === self::shop;
    }

    /**
     * Это шаблонная площадка?
     * !! Не злоупотреблять методом т.к. он обращается к удаленному серверу.
     *
     * @throws UserException
     *
     * @return bool
     */
    public static function isTemplateSite()
    {
        if (!INCLUSTER) {
            return false;
        }

        $oClient = gateway\Api::createClient();

        $bResultStatus = $mError = null;

        /* @noinspection PhpUnusedParameterInspection */
        $oClient->addMethod('HostTools', 'isTemplateSite', [], static function ($mResult, $error) use (&$bResultStatus, &$mError) {
            $mError = $error;
            $bResultStatus = $mResult;
        });

        if (!$oClient->doRequest()) {
            throw new UserException($oClient->getError());
        }
        if ($mError) {
            throw new UserException($mError);
        }

        return $bResultStatus;
    }
}
