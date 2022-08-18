<?php

namespace skewer\base\site;

use skewer\base\section\Parameters;
use skewer\base\SysVar;
use skewer\build\Tool\Domains\Api;
use skewer\components\auth\CurrentAdmin;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Класс, отвечающий за переменные, относящиеся к сайту.
 */
class Site
{
    /** @const string Поведение сайта при 404-ой ошибке: "Отдавать 404-ю страницу с кодом ответа 404" */
    const action_on_error404_respond_page_and_code404 = 'page_and_code404';

    /** @const string Поведение сайта при 404-ой ошибке: "Отдавать главную страницу с кодом ответа 404 (рекомендуется для Landing Page)" */
    const action_on_error404_respond_only_code404 = 'only_code404';

    const OLD_ADMIN_PANEL = 'oldadmin';
    const NEW_ADMIN_PANEL = 'admin';

    /**
     * Отдает название сайта.
     *
     * @return string
     */
    public static function getSiteTitle()
    {
        return Parameters::getValByName(
            Yii::$app->sections->languageRoot(),
            Parameters::settings,
            'site_name',
            true
        );
    }

    /**
     * Получить заголовок страницы админ. части по layout.
     *
     * @param string $sLayoutMode
     *
     * @return string
     */
    public static function getSiteAdmTitleByLayoutMode($sLayoutMode)
    {
        $sTitle = '';

        switch ($sLayoutMode) {
            case 'fileBrowser':
            case 'designFileBrowser':
                $sTitle = \Yii::t('adm', 'file_page_title');
                break;
            case 'galleryBrowser':
                $sTitle = \Yii::t('gallery', 'gallery_browser_title');
                break;
            case 'sliderBrowser':
                $sTitle = \Yii::t('slider', 'SliderBrowser.Cms.tab_name');
                break;
            case 'editorMap':
                $sTitle = Yii::t('editorMap', 'mapPanelTitle');
                break;
            default:
                $sTitle = \Yii::t('adm', 'admin_page_title');
        }

        $sTitle = sprintf('%s - %s', $sTitle, Site::getSiteTitle());

        return $sTitle;
    }

    public static function getSiteAdmDefaultTitle()
    {
        return sprintf('%s - %s', \Yii::t('adm', 'admin_page_title'), Site::getSiteTitle());
    }

    /**
     * Отдает email админа.
     *
     * @return string
     */
    public static function getAdminEmail()
    {
        return Parameters::getValByName(Yii::$app->sections->root(), Parameters::settings, 'email', false);
    }

    /**
     * Отдает email для отправки.
     *
     * @return string
     */
    public static function getNoReplyEmail()
    {
        return Parameters::getValByName(Yii::$app->sections->root(), Parameters::settings, 'send_email', false);
    }

    /**
     * Отдает текущую версию сборки в виде 3.19.3(dev)
     *      при BUILDNUMBER = 0019m3(dev).
     *
     * @return string
     */
    public static function getCmsVersion()
    {
        $sVersion = Yii::$app->version;
        if ($sVersion == '1.0') {
            $sVersion = BUILDNUMBER;
        }
        $sVersion = ltrim($sVersion, '0');
        $sVersion = preg_replace('/^(\d+)m(\d+.*)/', '$1.$2', $sVersion);
        $sVersion = preg_replace('/^(\d)(\d{2}.*)/', '$1.$2', $sVersion);
        $sVersion = preg_replace('/^(\d{2}.*)/', '3.$1', $sVersion);

        // режим файлового окружения сайта
        if (CurrentAdmin::isSystemMode()) {
            if (defined('SITE_MODE')) {
                $sMode = SITE_MODE;
            } else {
                if (INCLUSTER) {
                    if (USECLUSTERBUILD) {
                        $sMode = Yii::t('app', 'site_mode_cluster');
                    } else {
                        $sMode = Yii::t('app', 'site_mode_cluster_own_files');
                    }
                } else {
                    $sMode = Yii::t('app', 'site_mode_hosting');
                }
            }
            if ($sMode) {
                $sMode = '/' . $sMode;
            }
        } else {
            $sMode = '';
        }

        // тип конфигурации сайта
        $sType = Yii::t('app', 'site_type_' . Type::getAlias());

        $sOut = sprintf('%s [%s%s]', $sVersion, $sType, $sMode);

        if (CurrentAdmin::isSystemMode()) {
            if (YII_ENV_DEV) {
                $sOut .= ' {dev}';
            }
            if (YII_DEBUG) {
                $sOut .= ' {debug}';
            }
        }

        return $sOut;
    }

    /**
     * Отдает основной домен для площадки <br />
     * Example: "www.domain.com".
     *
     * @return string
     */
    public static function domain()
    {
        return Api::getCurrentDomain();
    }

    /**
     * Отдает основной домен для площадки <br />
     * Example: "http://www.domain.com".
     *
     * @return string
     */
    public static function httpDomain()
    {
        return WEBPROTOCOL . Api::getCurrentDomain();
    }

    /**
     * Отадет домен с http и / в конце <br />
     * Example: "http://www.domain.com/".
     *
     * @return string
     */
    public static function httpDomainSlash()
    {
        return self::httpDomain() . '/';
    }

    /**
     * Вернёт протокол доступа к сайту: http:// или https://.
     *
     * @return string
     */
    public static function getWebProtocol()
    {
        return WEBPROTOCOL;
    }

    /**
     * Отдает директорию релиза без директории skewer на конце.
     *
     * @return string
     */
    public static function getReleaseRootPath()
    {
        return mb_substr(RELEASEPATH, 0, mb_strlen(RELEASEPATH) - 7);
    }

    /**
     * Формирование url
     * для модулей из обрасти Tool в админке.
     *
     * @param $sNameModule
     * @param string $sLayout
     * @param string $sParam
     * @return string
     */
    public static function admUrl($sNameModule, $sLayout = 'tools', $sParam = '')
    {
        $sAdminAlias = (
            \Yii::$app->request->get('pathInfo')
            && strripos(\Yii::$app->request->pathInfo, Site::OLD_ADMIN_PANEL . '/') === 0
            ) ? Site::OLD_ADMIN_PANEL
            : Site::NEW_ADMIN_PANEL;

        $sDomain = Site::httpDomainSlash() ?: '/';

        $sLink = "{$sDomain}{$sAdminAlias}/#out.left.{$sLayout}={$sNameModule};out.tabs={$sLayout}_{$sNameModule}";
        if ($sParam) {
            $sLink .= ";init_tab={$sLayout}_{$sNameModule};init_param={$sParam}";
        }

        return $sLink;
    }

    /**
     * Формирование url на раздел в админке.
     *
     * @param int $iSectionId id раздела для вывода
     * @param string $sNameModule имя модуля
     * @param string $sPanelName имя модуля в левой панели section/lib/...
     * @param string $sParam инициализационные параметры для открытия
     * @param null $sLabel метка вывода модуля в админке
     *
     * @return string
     */
    public static function admTreeUrl($iSectionId, $sNameModule = '', $sPanelName = 'section', $sParam = '', $sLabel = null)
    {
        $sLink = Site::httpDomainSlash() . "admin/#out.left.{$sPanelName}={$iSectionId}";
        $objPath = $sLabel ? "{$sPanelName}_obj_{$sLabel}_" : $sPanelName;
        if ($sNameModule) {
            $sLink .= ";out.tabs={$objPath}_{$sNameModule}";
        }
        if ($sParam) {
            $sLink .= ";init_tab={$objPath}_{$sNameModule};init_param={$sParam}";
        }

        return $sLink;
    }

    /** Поведение сайта при 404-ой ошибке */
    public static function actionOnError404()
    {
        return SysVar::get('site.mode404', self::action_on_error404_respond_page_and_code404);
    }

    /**
     * Возвращает true если запрос пришел из React админского интерфейса
     * Если из ExtJS, то вернет false.
     *
     * @return bool
     */
    public static function isNewAdmin()
    {
        return (bool) ArrayHelper::getValue(
            \Yii::$app->getRequest()->getBodyParams(),
            ['data', 'skCmd']
        );
    }

    public static function getPartPathByAdminPanel()
    {
        return self::isNewAdmin()
            ? self::NEW_ADMIN_PANEL
            : self::OLD_ADMIN_PANEL;
    }
}
