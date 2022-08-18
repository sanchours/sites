<?php

namespace skewer\components\fonts;

use skewer\components\fonts\models\Fonts;
use yii\helpers\ArrayHelper;

class Api
{
    /** @var string Путь к файлам системных шрифтов */
    const PATH_FONTS = 'components/fonts/web/fonts/';

    /** @var string Тип шрифта "системный/внутренний" */
    const TYPE_FONT_INNER = 'inner';

    /** @var string Тип шрифта "внешний/закачиваемый" */
    const TYPE_FONT_EXTERNAL = 'external';

    /** @var string Дефолтное семейство serif */
    const FALLBACK_SERIF = 'serif';

    /** @var string Дефолтное семейство sans-serif */
    const FALLBACK_SANS_SERIF = 'sans-serif';

    /**
     * Вернет активные шрифты с дефолтным семейством
     *
     * @return array
     */
    public static function getActiveFontsNameWithDefFamily()
    {
        $aActiveFonts = self::getListFonts(true);

        $aActiveFontsWithDefFamily = ArrayHelper::getColumn($aActiveFonts, static function ($item) {
            if ($item['fallback']) {
                $sOut = sprintf('%s, %s', $item['name'], $item['fallback']);
            } else {
                $sOut = $item['name'];
            }

            return $sOut;
        });

        return $aActiveFontsWithDefFamily;
    }

    /**
     * Получить список шрифтов.
     *
     * @param $bOnlyActive - только активные?
     * @param null $mType - тип шрифтов?
     *
     * @return array
     */
    public static function getListFonts($bOnlyActive, $mType = null)
    {
        $oQuery = Fonts::find();

        if ($bOnlyActive) {
            $oQuery->where(['active' => 1]);
        }

        if ($mType && in_array($mType, [self::TYPE_FONT_INNER, self::TYPE_FONT_EXTERNAL])) {
            $oQuery
                ->andWhere(['type' => $mType]);
        }

        $aActiveFonts = $oQuery->asArray()->all();

        return $aActiveFonts;
    }

    /**
     * Получить директорию загружаемых шрифтов.
     *
     * @return string
     */
    public static function getDirPathDownloadedFonts()
    {
        return FILEPATH . 'fonts/';
    }

    /**
     * Получить директорию системных шрифтов.
     *
     * @return string
     */
    public static function getDirPathSystemFonts()
    {
        return RELEASEPATH . self::PATH_FONTS;
    }

    /**
     * Получить директории загруженных шрифтов.
     *
     * @return array
     */
    public static function getDirectoriesDownloadedFonts()
    {
        $aDirs = scandir(Api::getDirPathDownloadedFonts());

        $aDirs = array_filter($aDirs, static function ($item) {
            return $item != '.' && $item != '..';
        });

        return $aDirs;
    }

    /**
     * Создать директорию для загружаемых шрифтов.
     *
     * @return bool
     */
    public static function createDirectoryDownloadedFonts()
    {
        $sDir = self::getDirPathDownloadedFonts();

        // Директория создана?
        if (is_dir($sDir)) {
            $bDirectoryExist = true;
        } else {
            $bDirectoryExist = @mkdir($sDir);
        }

        $bFileModeInstalled = false;

        if ($bDirectoryExist) {
            // Права на директорию установлены?
            $bFileModeInstalled = @chmod($sDir, 0777);
        }

        return $bDirectoryExist && $bFileModeInstalled;
    }

    /**
     * Получить список дефолтных семейств.
     *
     * @return array
     */
    public static function getListFallback()
    {
        $aListFallBack = [
            self::FALLBACK_SERIF,
            self::FALLBACK_SANS_SERIF,
        ];

        return array_combine($aListFallBack, $aListFallBack);
    }
}
