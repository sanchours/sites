<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 19.05.2017
 * Time: 15:49.
 */

namespace skewer\components\config\installer;

use skewer\components\search\models\SearchIndex;

/**
 * Класс-прослойка для запуска функций после установки модулей
 * Class Service.
 */
class Service
{
    public static function rebuildSearchIndex()
    {
        \skewer\components\seo\Service::rebuildSearchIndex();
    }

    public static function resetActive()
    {
        SearchIndex::updateAll(['status' => 0], ['status' => 1]);
    }

    public static function makeSearchIndex()
    {
        \skewer\components\seo\Service::makeSearchIndex();
    }

    public static function makeSitemap()
    {
        \skewer\components\seo\Service::makeSiteMap();
    }
}
