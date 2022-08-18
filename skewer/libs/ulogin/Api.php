<?php

namespace skewer\libs\ulogin;

use skewer\base\site_module\Parser;
use skewer\base\SysVar;

class Api
{
    public static $nameAuthSocialNetwork = 'socialNetwork.authSocialNetwork';
    public static $nameTypeDisplay = 'socialNetwork.typeDisplay';
    public static $nameTypeTheme = 'socialNetwork.typeTheme';

    public static function getListTypesDisplay()
    {
        return [
            ULogin::D_SMALL => \Yii::t('socialNetwork','display_small'),
            ULogin::D_PANEL => \Yii::t('socialNetwork','display_panel'),
            ULogin::D_WINDOW => \Yii::t('socialNetwork','display_window')
        ];
    }

    public static function getListTypesTheme()
    {
        return [
            ULogin::THEME_CLASSIC => \Yii::t('socialNetwork','theme_classic'),
            ULogin::THEME_FLAT => \Yii::t('socialNetwork','theme_flat')
        ];
    }

    public static function getTemplate4AuthSocialNetwork()
    {
        if (SysVar::get(self::$nameAuthSocialNetwork)) {
            return Parser::parseTwig('socialNetwork.twig', [
                'errSocialNetwork' => \Yii::$app->session->getFlash('errSocialNetwork'),
                'socialNetwork' => self::getWidgetULoginData()
            ], __DIR__ . '/templates'
            );
        }

        return '';
    }

    /**
     * @return string
     * @throws \Exception
     */
    private static function getWidgetULoginData()
    {
        $protocol = strstr(\Yii::$app->getRequest()->hostInfo, ':', true);

        return ULogin::widget([
            'display' => SysVar::get(self::$nameTypeDisplay),
            'theme' => SysVar::get(self::$nameTypeTheme),
            // required fields
            'fields' => [ULogin::F_FIRST_NAME, ULogin::F_LAST_NAME, ULogin::F_EMAIL, ULogin::F_PHONE],
            // optional fields
            'optional' => [ULogin::F_BDATE],
            // login providers
            'providers' => [
                ULogin::P_FACEBOOK,
                ULogin::P_INSTAGRAM,
                ULogin::P_GOOGLE,
                ULogin::P_VKONTAKTE,
                ULogin::P_YANDEX,
                ULogin::P_MAILRU
            ],
            // login providers that are shown when user clicks on additonal providers button
            'hidden' => [],
            // where to should ULogin redirect users after successful login
            'redirectUri' => ['auth/ulogin'],
            // force use https in redirect uri
            'forceRedirectUrlScheme' => $protocol,
            'language' => ULogin::L_RU,
            // providers sorting ('relevant' by default)
            'sortProviders' => ULogin::S_DEFAULT,
            // verify users' email (disabled by default)
            'verifyEmail' => '0',
            // mobile buttons style (enabled by default)
            'mobileButtons' => '1'
        ]);
    }

}
