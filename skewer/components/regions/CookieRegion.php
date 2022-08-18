<?php

namespace skewer\components\regions;

use skewer\base\site\Site;
use yii\web\Cookie;

class CookieRegion
{
    const SHOW_FANCYBOX = 'show_fancy';
    const REGION = 'region';

    /**
     * Проверяет показ фенсибокса, записанного в cookie
     * из ответа или, если их нет, из запроса.
     *
     * @return null|string
     */
    public static function wasShownFancybox()
    {
        $showingFancybox = \Yii::$app->request->cookies->getValue(
            self::SHOW_FANCYBOX,
            false
        );
        if (!$showingFancybox) {
            return \Yii::$app->response->cookies->getValue(
                self::SHOW_FANCYBOX,
                false
            );
        }

        return $showingFancybox;
    }

    public static function setShowFancybox()
    {
        \Yii::$app->getResponse()->getCookies()->add(new Cookie([
            'name' => self::SHOW_FANCYBOX,
            'value' => true,
            'domain' => Site::domain(),
            'expire' => self::getDurationOfStorage(),
        ]));
    }

    public static function removeShowFancybox()
    {
        \Yii::$app->getResponse()->getCookies()->add(new Cookie([
            'name' => self::SHOW_FANCYBOX,
            'value' => false,
            'domain' => Site::domain(),
            'expire' => self::getDurationOfStorage(),
        ]));
    }

    /**
     * Возвращает значение региона, записанного в cookie
     * из ответа или, если их нет, из запроса.
     *
     * @return null|string
     */
    public static function getRegionCookies()
    {
        $installingRegion = \Yii::$app->getResponse()->getCookies()->getValue(
            self::REGION
        );

        if ($installingRegion === null) {
            $installingRegion = \Yii::$app->getRequest()->getCookies()->getValue(
                self::REGION
            );
        }

        return $installingRegion;
    }

    /**
     * Запись в cookies domain метки региона.
     *
     * @param $domain
     */
    public static function setDomainOfRegion($domain)
    {
        \Yii::$app->response->cookies->add(new Cookie([
            'name' => self::REGION,
            'value' => $domain,
            'domain' => Site::domain(),
            'expire' => self::getDurationOfStorage(),
        ]));
    }

    private static function getDurationOfStorage()
    {
        $year = 86400 * 365;

        return time() + $year;
    }
}
