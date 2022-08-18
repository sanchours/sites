<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.07.2017
 * Time: 10:23.
 */

namespace skewer\build\Tool\Utils;

use skewer\base\site_module\Parser;
use skewer\base\SysVar;
use skewer\components\design\model\Params;
use skewer\components\gallery\Photo;
use yii\base\UserException;

class Api
{
    /** Форматы фавиконки */
    public static function faviconFormats()
    {
        return [
            'square310x310logo',
            'wide310x150logo',
            'square150x150logo',
            'square70x70logo',
            'apple_57x57',
            'apple_60x60',
            'apple_72x72',
            'apple_76x76',
            'apple_114x114',
            'apple_120x120',
            'apple_144x144',
            'apple_152x152',
            'apple_180x180',
            'favicon_32x32',
            'android_192x192',
            'favicon_96x96',
            'favicon_16x16',
            'mstile_144x144',
        ];
    }

    public static function rebuildFavicon()
    {
        /*Делаем manifest.json*/
        $oFaviconParam = Params::find()
            ->where(['name' => 'page.favicon'])
            ->one();

        $aData = Photo::getFromAlbum($oFaviconParam->value);
        if (!isset($aData[0])) {
            throw new UserException(\Yii::t('utils', 'no_favicon_photos'));
        }
        $aPhotos = $aData[0]->getAttribute('images_data');

        $aIcons = [];

        foreach (self::faviconFormats() as $sFormat) {
            if (!isset($aPhotos[$sFormat])) {
                throw new UserException(\Yii::t('utils', 'not_exist_format', ['sFormat' => $sFormat]));
            }

            $sFullFilePath = WEBPATH . $aPhotos[$sFormat]['file'];

            if (!file_exists($sFullFilePath)) {
                throw new UserException(\Yii::t('utils', 'image_not_exist', ['sFormat' => $sFormat, 'sFullFilePath' => $sFullFilePath]));
            }

            $sSrc = $aPhotos[$sFormat]['file'];
            $sSizes = $aPhotos[$sFormat]['width'] . 'x' . $aPhotos[$sFormat]['height'];
            $sMimeType = mime_content_type($sFullFilePath);

            $aIcons[$sFormat] = [
                'src' => $sSrc,
                'sizes' => $sSizes,
                'type' => $sMimeType,
            ];
        } // end foreach

        $aOut = [
            'lang' => \Yii::$app->language,
            // "background_color"=>"#ffffff",
            'name' => \skewer\base\site\Site::getSiteTitle(),
            'short_name' => \skewer\base\site\Site::getSiteTitle(),
            'display' => 'standalone',
            'icons' => $aIcons,
        ];

        if (!file_exists(FILEPATH . 'manifest.json')) {
            $fp = fopen(FILEPATH . 'manifest.json', 'a+');
            fwrite($fp, json_encode($aOut));
            fclose($fp);
        } else {
            file_put_contents(FILEPATH . 'manifest.json', json_encode($aOut));
        }

        /*Делаем browserconfig*/
        $aData = [
            'square70x70logo' => $aPhotos['square70x70logo']['file'],
            'square150x150logo' => $aPhotos['square150x150logo']['file'],
            'wide310x150logo' => $aPhotos['wide310x150logo']['file'],
            'square310x310logo' => $aPhotos['square310x310logo']['file'],
        ];

        $sOut = Parser::parseTwig('browserconfig.twig', $aData, __DIR__ . '/templates');

        if (!file_exists(FILEPATH . 'browserconfig.xml')) {
            $fp = fopen(FILEPATH . 'browserconfig.xml', 'a+');
            fwrite($fp, $sOut);
            fclose($fp);
        } else {
            file_put_contents(FILEPATH . 'browserconfig.xml', $sOut);
        }

        $sFaviconHtml = Parser::parseTwig('favicon.twig', ['aIcons' => $aIcons], __DIR__ . '/templates');

        SysVar::set('favicon.html', $sFaviconHtml);
    }

    /**
     * Сброс кэшей.
     */
    public static function dropCache()
    {
        \Yii::$app->router->updateModificationDateSite();
        \Yii::$app->rebuildRegistry();
        \Yii::$app->rebuildCss();
        \Yii::$app->rebuildLang();
        \Yii::$app->clearParser();

        \Yii::$app->cache->flush();
    }
}
