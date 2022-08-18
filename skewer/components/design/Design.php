<?php

namespace skewer\components\design;

use skewer\base\section\Page;
use skewer\base\section\Parameters;
use skewer\base\SysVar;

/**
 * API класс для дизайнерского режима.
 */
class Design
{
    /** имя метки для хранения последнего времени обновления */
    const lastUpdatedTime = 'last_updated_time';

    /** версия сайта - обычный сайт */
    const versionDefault = 'default';

    /** путь к директории хранения css файлов */
    const cssDirPath = 'files/css/';

    /** имя директории для хранения изображений дизайнерского режима */
    const imageDirName = 'design';

    /**
     * Лидает надор доступных слоев.
     *
     * @return array
     */
    public static function getVersionList()
    {
        return [
            self::versionDefault,
        ];
    }

    /**
     * Получить иконку сайта или тип изображения иконки сайта.
     *
     * @param bool $bGetType Получить тип иконки сайта?
     *
     * @return string
     */
    public static function getFavicon($bGetType = false)
    {
        /* @var string url адрес иконки сайта */
        static $sUrl;

        $sUrl = $sUrl ?: self::get('page', 'favicon');

        // Определить тип изображения по расширению файла или получить из настроек
        if ($bGetType and $sUrl) {
            switch (mb_strtolower(mb_substr($sUrl, -4))) {
                case '.ico':
                    return 'image/x-icon';

                case '.gif':
                    return 'image/gif';

                case 'jpeg':
                case '.jpg':
                    return 'image/jpeg';

                case '.png':
                    return 'image/png';

                case '.bmp':
                    return 'image/bmp';

                default:
                    return '';
            }
        }

        return $sUrl;
    }

    /**
     * @static Метод получения значения css-параметра
     *
     * @param string $sGroup Название группы для параметра
     * @param string $sKey Название параметра
     * @param string $sPathAsset путь до ассета
     * @param string $sLayer Название слоя
     *
     * @return null|string
     */
    public static function get($sGroup, $sKey, $sPathAsset = '', $sLayer = 'default')
    {
        $aParam = DesignManager::getParam($sGroup . '.' . $sKey, $sLayer);

        if (!isset($aParam['value'])) {
            return;
        }

        if (isset(\Yii::$app->view->assetBundles[$sPathAsset])
            &&
            !mb_substr_count($aParam['value'], 'files/')
        ) {
            if (mb_substr_count($aParam['value'], '../')) {
                $aParam['value'] = str_replace('../', '/', $aParam['value']);
            }

            return \Yii::$app->view->assetBundles[$sPathAsset]->baseUrl . $aParam['value'];
        }

        return $aParam['value'];
    }

    /**
     * Отдает логотип сайта.
     *
     * @return false|string
     */
    public static function getLogo()
    {
        $baseUrl = \Yii::$app->view->assetBundles['skewer\build\Page\Main\Asset']->baseUrl;
        $sPathImg = Parameters::getValByName(\Yii::$app->sections->getValue(Page::LANG_ROOT), '.', 'site_nlogo');
        if (mb_stristr($sPathImg, 'files')) {
            return $sPathImg;
        }

        return $baseUrl . $sPathImg;
    }

    /** имя флага активности режима дизайнера */
    protected static $sGlobalFlagName = '__design_mode_active';

    /**
     * Устанавливает глобальных флаг активации дизайнерского режима.
     *
     * @static
     */
    public static function setModeGlobalFlag()
    {
        $_SESSION[self::$sGlobalFlagName] = true;
    }

    /**
     * Сбрасывает флаг активности дизайнерского режима.
     */
    public static function unsetModeGlobalFlag()
    {
        if (isset($_SESSION[self::$sGlobalFlagName])) {
            unset($_SESSION[self::$sGlobalFlagName]);
        }
    }

    /**
     * Отдает true, если режим дизайнера активен.
     *
     * @static
     *
     * @return bool
     */
    public static function modeIsActive()
    {
        return  isset($_SESSION[self::$sGlobalFlagName]) and $_SESSION[self::$sGlobalFlagName];
    }

    /**
     * Отдает имя директории дизайнерского режима для клиентской части.
     *
     * @static
     *
     * @return string
     */
    public static function getDirList()
    {
        $sDir = '/skewer/build/Design/Frame/';

        return [
            'jsDir' => $sDir . 'js/design/',
            'cssDir' => $sDir . 'css/design/',
        ];
    }

    /**
     * По url вычисляет состояние.
     *
     * @param string $sUrl
     *
     * @return string
     */
    public static function getVersionByUrl(/* @noinspection PhpUnusedParameterInspection */
        $sUrl
    ) {
        return self::versionDefault;
    }

    /**
     * Отдает название версии сайта по псевдониму.
     *
     * @param $sType
     *
     * @return string
     */
    public static function getVersionTitle($sType)
    {
        switch ($sType) {
            case self::versionDefault:
                return 'Обычная версия';
            default:
                return '-не определена-';
        }
    }

    /**
     * Отдает заведомо допустимый тип отображения.
     *
     * @param $sType
     *
     * @return string
     */
    public static function getValidVersion($sType)
    {
        if (!in_array($sType, self::getVersionList())) {
            return self::versionDefault;
        }

        return $sType;
    }

    /**
     * Отдает системный путь к директории дополнительных файлов.
     *
     * @return string
     */
    public static function getAddCssDirPath()
    {
        return WEBPATH . self::cssDirPath;
    }

    /**
     * Отдает имя файла типа add_default.css.
     *
     * @param $sViewMode
     *
     * @return string
     */
    public static function getLocalCssFileName($sViewMode)
    {
        return sprintf('add_%s.css', self::getValidVersion($sViewMode));
    }

    /**
     * Отдает имя файла с директорией типа files/css/add_default.css.
     *
     * @param $sViewMode
     *
     * @return string
     */
    public static function getLocalCssFileNameWithDir($sViewMode)
    {
        return self::cssDirPath . self::getLocalCssFileName($sViewMode);
    }

    /**
     * @param string $sViewMode
     *
     * @return string
     */
    public static function getAddCssFilePath($sViewMode = 'default')
    {
        return WEBPATH . self::getLocalCssFileNameWithDir($sViewMode);
    }

    /**
     * Возвращает время последнего обновления.
     *
     * @return bool|string
     */
    public static function getLastUpdatedTime()
    {
        return SysVar::get(self::lastUpdatedTime);
    }

    /**
     * Устанавливает время последнего обновления.
     *
     * @param null $sVal желаемое значение (если не указано - time())
     */
    public static function setLastUpdatedTime($sVal = null)
    {
        if (!func_num_args()) {
            $sVal = time();
        }
        SysVar::set(self::lastUpdatedTime, (string) $sVal);
    }

    /**
     * Выдает текст переданной переменной только если работаем в диз режиме
     * Нужен для того, чтобы убрать из стандатного вывода метки типа sktag, на которые ругаются все валидаторы
     * варианты оборачвания.
     *
     * {{ Design.write(' sktag="modules.forms"') }}
     *
     * twig:
     *  * {{ Design.write('') }}
     *  * {% if Design.modeIsActive() %}{% endif %}
     *
     * php:
     *  * <?= Design::write('') ?>
     *  * <?php if (Design::modeIsActive()): ?><?php endif; ?>
     *
     * @param string $sText
     *
     * @return string
     */
    public static function write($sText)
    {
        return (self::modeIsActive() || YII_ENV == 'test') ? $sText : '';
    }

    /**
     * Добавляет asset по имени класса на страницу.
     *
     * @param $sClassName
     */
    public static function addAsset($sClassName)
    {
        \Yii::$app->view->registerAssetBundle($sClassName);
    }
}
