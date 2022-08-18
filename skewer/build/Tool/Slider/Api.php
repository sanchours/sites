<?php

namespace skewer\build\Tool\Slider;

use skewer\base\orm;
use skewer\base\section\Tree;
use skewer\helpers\UploadedFiles;

class Api
{
    /** Папка для слайдов */
    const path = 'slider';

    const TARGET_TYPE_BLANK = '_blank';
    const TARGET_TYPE_SELF = '_self';

    /**
     * Вернёт список типов навигации.
     *
     * @return array
     */
    public static function getNavigations()
    {
        return [
            'dots' => \Yii::t('slider', 'fotorama_nav_dots'),
            'thumbs' => \Yii::t('slider', 'fotorama_nav_thumbs'),
            'false' => \Yii::t('slider', 'fotorama_nav_disable'),
        ];
    }

    /**
     * Вернёт список вариантов отображения стрелок.
     *
     * @return array
     */
    public static function getArrows()
    {
        return [
            'always' => \Yii::t('slider', 'fotorama_arrows_always'),
            'true' => \Yii::t('slider', 'fotorama_arrows_hover'),
            'false' => \Yii::t('slider', 'fotorama_arrows_disable'),
        ];
    }

    /**
     * Вернёт список типов анимации.
     *
     * @return array
     */
    public static function getTransitions()
    {
        return [
            'slide' => \Yii::t('slider', 'fotorama_transition_slide_effect'),
            'crossfade' => \Yii::t('slider', 'fotorama_transition_crossfade_effect'),
            'dissolve' => \Yii::t('slider', 'fotorama_transition_dissolve_effect'),
        ];
    }

    /**
     * Отдает путь до изображения.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public static function getEmptyImgWebPath()
    {
        return \Yii::$app->getAssetManager()->getBundle(Asset::className())->baseUrl . '/img/noimg.png';
    }

    /**
     * Загружает изображения и перемещает в целевую директорию.
     *
     * @static
     *
     * @throws \Exception
     *
     * @return bool|string
     */
    public static function uploadFile()
    {
        // параметры загружаемого файла
        $aFilter = [];
        $aFilter['size'] = \Yii::$app->getParam(['upload', 'maxsize']);
        $aFilter['allowExtensions'] = \Yii::$app->getParam(['upload', 'allow', 'images']);
        $aFilter['imgMaxWidth'] = \Yii::$app->getParam(['upload', 'images', 'maxWidth']);
        $aFilter['imgMaxHeight'] = \Yii::$app->getParam(['upload', 'images', 'maxHeight']);

        $aValues = \skewer\build\Adm\Files\Api::getLanguage4Uploader();
        UploadedFiles::loadErrorMessages($aValues);

        // загрузка
        $oFiles = UploadedFiles::get($aFilter, FILEPATH, PRIVATE_FILEPATH);

        // если ни один файл не загружен - выйти
        if (!$oFiles->count()) {
            throw new \Exception($oFiles->getError());
        }
        /*
         * пока берется только один файл, но делался задел на загрузку
         * большего количества
         */

        // имя файла исходника
        $sSourceFN = false;

        /* @noinspection PhpUnusedLocalVariableInspection */
        foreach ($oFiles as $file) {
            // проверка ошибок
            if ($sError = $oFiles->getError()) {
                throw new \Exception($sError);
            }
            $iSectionId = \Yii::$app->sections->main();
            $sSourceFN = $oFiles->UploadToSection($iSectionId, self::path, false);
        }

        // проверка наличия имени файла
        if (!$sSourceFN) {
            throw new \Exception(\Yii::t('gallery', 'noLoadImage'));
        }
        // отрезать корневую папку от пути
        $sSourceFN = mb_substr($sSourceFN, mb_strlen(WEBPATH) - 1);

        return $sSourceFN;
    }

    /**
     * Вернет размеры первого изображения из массива $aImages.
     *
     * @param $aImages
     *
     * @return array
     */
    public static function getDimensionsFirstImage($aImages)
    {
        $aFirstImage = reset($aImages);
        $sPath = WEBPATH . $aFirstImage['img'];

        if (!file_exists($sPath)) {
            return [
                'width' => 0,
                'height' => 0,
                'ratio' => 0,
            ];
        }

        $aImageInfo = getimagesize($sPath);

        return [
            'width' => $aImageInfo[0],
            'height' => $aImageInfo[1],
            'ratio' => $aImageInfo[0] / $aImageInfo[1],
        ];
    }

    /**
     * Вернёт настройки показа слайдера.
     *
     * @param  array $aBanner - текущий баннер
     *
     * @return array
     */
    public static function getAllTools($aBanner = [])
    {
        $aToolData = [];
        $aItems = orm\Query::SelectFrom('banners_tools')->getAll();

        foreach ($aItems as $aItem) {
            $mVal = $aItem['bt_value'];
            if (is_numeric($mVal)) {
                $mVal = (int) $mVal;
            }
            $aToolData[$aItem['bt_key']] = $mVal;
        }

        if (!empty($aToolData['maxHeight'])) {
            $aToolData['maxHeight'] = (int) $aToolData['maxHeight'];
        } else {
            unset($aToolData['maxHeight']);
        }

        $fPrependKey = static function ($mKey) {
            if (($mKey === 'false') || ($mKey === 0) || ($mKey === '0')) {
                return false;
            }
            if (($mKey === 'true') || ($mKey === 1) || ($mKey === '1')) {
                return true;
            }

            return $mKey;
        };

        if (isset($aToolData['loop'])) {
            $aToolData['loop'] = $fPrependKey($aToolData['loop']);
        }

        if (isset($aBanner['bullet'])) {
            $aToolData['nav'] = $fPrependKey($aBanner['bullet']);
        }

        if (isset($aBanner['scroll'])) {
            $aToolData['arrows'] = $fPrependKey($aBanner['scroll']);
        }

        // ограничения высоты
        foreach ($aToolData as $sOptionName => &$sOptionValue) {
            if (mb_strpos($sOptionName, 'minHeight') === 0) {
                $aToolData['height_limits'][$sOptionName] = $sOptionValue;
                unset($aToolData[$sOptionName]);
            }
        }

        return $aToolData;
    }

    /**
     * Получение дерева разделов в виде списка.
     *
     * @return array
     */
    public static function getSectionTitle()
    {
        return Tree::getSectionsTitle(\Yii::$app->sections->root(), true);
    }

    /**
     * Отдает массив типов атрибута target ссылок.
     *
     * @return array
     */
    public static function getLinkTargetTypes()
    {
        return [
            self::TARGET_TYPE_BLANK => \Yii::t('slider', 'link_target_title_blank'),
            self::TARGET_TYPE_SELF => \Yii::t('slider', 'link_target_title_self'),
        ];
    }
}
