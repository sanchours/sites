<?php

namespace skewer\build\Adm\Gallery;

use skewer\base\section\Parameters;
use skewer\components\gallery;
use skewer\helpers\Files;
use skewer\helpers\Image;
use skewer\helpers\UploadedFiles;
use yii\helpers\ArrayHelper;

/**
 * API работы с админской частью галереи.
 */
class Api
{
    /** Подпапка для хранения временного формата для кропа */
    const sCropFormatName = 'crop_min';

    /** Имя папки для хранения всех альбомных галерей сайта */
    const DIR_NAME = 'gallery';

    /**
     * Высота элемента для кропа.
     */
    const cropHeight = 400;

    /**
     * Список ошибок.
     *
     * @var array
     */
    private static $aErrorUploadList = [];

    /**
     * Путь до папки с временными картинками.
     *
     * @var string
     */
    public static $sTempPath = 'files/temp/';

    /**
     * Время жизни временных картинок.
     *
     * @var int
     */
    public static $iTempLiveTime = 3600;

    /**
     * @return array
     */
    public static function getErrorUploadList()
    {
        return static::$aErrorUploadList;
    }

    /**
     * Получить полный путь к папке альбома.
     *
     * @param int $iAlbumId Id альбома
     * @param bool $bWithRoot Начиная с корня?
     *
     * @return string
     */
    public static function getAlbumDir($iAlbumId, $bWithRoot = true)
    {
        return $bWithRoot ?
            FILEPATH . Api::DIR_NAME . \DIRECTORY_SEPARATOR . $iAlbumId :
            Api::DIR_NAME . \DIRECTORY_SEPARATOR . $iAlbumId;
    }

    /**
     * Обновляет фотокарточку после автоматического кропа.
     *
     * @param mixed $aData
     * @param mixed $aFormat
     * @param mixed $iAlbumId
     */
    public static function operateAfterRecrop($aData, $aFormat, $iAlbumId)
    {
        $sImageFullPath = ROOTPATH . 'web' . $aData['source'];

        $oImage = new Image();
        if (!$oImage->load($sImageFullPath)) {
            throw new \Exception(\Yii::t('gallery', 'photos_error_imgload'));
        }
        $oImage->saveToBuffer();

        list($iWidth, $iHeight) = $oImage->getSize();

        /*Если оба размера нулевые, изображение вообще не изменится*/
        if (($aData['format']['width'] == 0) and ($aData['format']['height'] == 0)) {
            $aData['format']['width'] = $iWidth;
            $aData['format']['height'] = $iHeight;
        }

        /*Надо рассчитать высоту и ширину картинки которую вырежем из исходника*/
        $aScaleData = $oImage->operateCalculation($aData['format']['width'], $aData['format']['height'], $aData['cropdata']['width'], $aData['cropdata']['height'], true);

        $aDataOperate = [
            'img_width' => $aData['cropdata']['width'],
            'img_height' => $aData['cropdata']['height'],
            'left_delay' => $aData['cropdata']['x'] * (-1),
            'top_delay' => $aData['cropdata']['y'] * (-1),
            'img_need_width' => $aScaleData['img_width'],
            'img_need_height' => $aScaleData['img_height'],
        ];

        //возьмем размеры изображения которые выбраны на поле
        $oImage->iFormatWidth = $aScaleData['img_width'];
        $oImage->iFormatHeight = $aScaleData['img_height'];

        if ($aFormat['width'] && $aFormat['height']) {
            //в формате ОБЕ величины фиксированы
            $oImage->iFormatWidth = $aFormat['width'];
            $oImage->iFormatHeight = $aFormat['height'];
        } elseif ($aFormat['width'] || $aFormat['height']) {
            //в формате ОДНА величина фиксирована
            if ($aFormat['width']) {
                //фиксирована ширина
                //найден коэф изменения по ширине
                $fCoef = $aFormat['width'] / $oImage->iFormatWidth;

                $oImage->iFormatWidth = $aFormat['width'];
                $oImage->iFormatHeight = $oImage->iFormatHeight * $fCoef;
            } elseif ($aFormat['height']) {
                //фиксирована высота
                $fCoef = $aFormat['height'] / $oImage->iFormatHeight;

                $oImage->iFormatHeight = $aFormat['height'];
                $oImage->iFormatWidth = $oImage->iFormatWidth * $fCoef;
            }
        }

        $oImage->operateImg($aDataOperate, $aFormat['width'], $aFormat['height'], true, $aFormat['scale_and_crop']);

        list($iWidth, $iHeight) = $oImage->getSize();

        $oImage->updSizes($iWidth, $iHeight);

        $aProfile = gallery\Profile::getById($aFormat['profile_id']);

        if ($aFormat['use_watermark']) {
            $oImage->applyWatermark($aFormat['watermark'], gallery\Photo::hexToRgb($aProfile['watermark_color']), $aFormat['watermark_align']);
        }

        $aAlbum = gallery\Photo::getImage($aData['id']);
        $aImagesData = json_decode($aAlbum->getAttribute('images_data'), true);

        if (isset($aImagesData[$aData['format']['name']]['file'])) {
            $sFileName = WEBPATH . $aImagesData[$aData['format']['name']]['file'];
        } else {
            $sFileName = FILEPATH . Api::DIR_NAME . \DIRECTORY_SEPARATOR . $iAlbumId . time() . '.' . $oImage->getImageType();
            $aImagesData[$aData['format']['name']]['file'] = $sFileName;
        }

        $aImagesData[$aData['format']['name']]['width'] = $iWidth;
        $aImagesData[$aData['format']['name']]['height'] = $iHeight;

        $aAlbum->setAttribute('images_data', json_encode($aImagesData));

        /*Если рекропнули thumbnail_overlay, значит надо изменить и фотку thumbnail*/
        if ($aFormat['name'] == 'thumbnail_overlay') {
            $aAlbum->setAttribute('thumbnail', '/' . str_replace(WEBPATH, '', str_replace('//', '/', $sFileName)));
        }

        $aAlbum->save();

        if (!$oImage->save($sFileName)) {
            throw new \Exception(\Yii::t('gallery', 'photos_error_imgsave'));
        }
    }

    /**
     * Загружает изображения и перемещает в целевую директорию.
     *
     * @static
     *
     * @param $iAlbumId
     *
     * @throws \Exception
     *
     * @return bool|string
     */
    public static function uploadFile($iAlbumId)
    {
        // параметры загружаемого файла
        $aCommonFilter = [];
        $aCommonFilter['size'] = \Yii::$app->getParam(['upload', 'maxsize']);
        $aCommonFilter['allowExtensions'] = \Yii::$app->getParam(['upload', 'allow', 'images']);
        $aCommonFilter['imgMaxWidth'] = \Yii::$app->getParam(['upload', 'images', 'maxWidth']);
        $aCommonFilter['imgMaxHeight'] = \Yii::$app->getParam(['upload', 'images', 'maxHeight']);

        /** Спец. ограничения на профиль */
        $ProfileFilter = gallery\Profile::getUploadLimiting(gallery\Album::getProfileId($iAlbumId));
        $aFilter = ArrayHelper::merge($aCommonFilter, $ProfileFilter);

        $aValues = \skewer\build\Adm\Files\Api::getLanguage4Uploader();
        UploadedFiles::loadErrorMessages($aValues);

        // загрузка
        $oFiles = UploadedFiles::get($aFilter, dirname(self::getAlbumDir($iAlbumId)), PRIVATE_FILEPATH);

        // если ни один файл не загружен - выйти
        if (!$oFiles->count()) {
            throw new \Exception($oFiles->getError());
        }
        /*
         * пока берется только один файл, но делался задел на загрузку
         * большего количества
         */

        // имя файла исходника
        $sSourceFN = [];

        $aErrorMessages = [];

        foreach ($oFiles as $file) {
            try {
                // проверка ошибок
                if ($sError = $oFiles->getError()) {
                    throw new \Exception($sError);
                }
                $sNewFile = $oFiles->UploadToSection(self::getAlbumDir($iAlbumId, false), 'sources', false, false, true);

                // отрезать корневую папку от пути
                $sNewFile = mb_substr($sNewFile, mb_strlen(WEBPATH) - 1);
                /* @noinspection PhpIllegalArrayKeyTypeInspection */
                $sSourceFN[$file['name']] = $sNewFile;
            } catch (\Exception $e) {
                $aErrorMessages[] = $e->getMessage();
            }
        }

        static::$aErrorUploadList = $aErrorMessages;

        reset($sSourceFN);

        return (count($sSourceFN) == 1 && !count($aErrorMessages)) ? $sSourceFN[key($sSourceFN)] : $sSourceFN;
    }

    /**
     * Создает миниатюру для режима обрезки.
     *
     * @static
     *
     * @param string $sSourceFN имя исходного файла
     * @param int $iAlbumId Id альбома
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function createCropMin($sSourceFN, $iAlbumId)
    {
        $bProtected = false;
        $sSourceFN = WEBPATH . $sSourceFN;

        /* Путь к корневой директории галереи в текущем разделе */
        $sImagePath = self::getAlbumDir($iAlbumId) . \DIRECTORY_SEPARATOR;

        /* Загрузка исходного изображения для дальнейшей обработки */
        $aValues = \skewer\build\Adm\Files\Api::getLanguage4Image();
        Image::loadErrorMessages($aValues);
        $oImage = new Image();

        // загрузить изображение
        if (!$oImage->load($sSourceFN)) {
            throw new \Exception('Crop. Image processing error: Image not loaded!');
        }
        // привести высоту к приемлемому размеру
        $iHeight = min($oImage->getSrcHeight(), self::cropHeight);

        // изменить размер
        $oImage->resize(0, $iHeight);

        $sSavedFile = Files::generateUniqFileName($sImagePath . self::sCropFormatName . \DIRECTORY_SEPARATOR, basename($sSourceFN));

        $sSavedFile = str_replace(Files::getRootUploadPath($bProtected), '', $sSavedFile);

        $sDir = Files::createFolderPath(dirname($sSavedFile), $bProtected);

        if (!$sDir) {
            throw new \Exception('Crop. Image processing error: Directory is not created!');
        }
        $sNewFilePath = $sDir . \DIRECTORY_SEPARATOR . basename($sSavedFile);

        /* Сохранить измененное изображение */
        $sNewFilePath = $oImage->save($sNewFilePath);
        if (!$sNewFilePath) {
            throw new \Exception('Crop. Image processing error: Image do not saved!');
        }
        list($iWidth, $iHeight) = $oImage->getSize();

        return [
            'file' => Files::getWebPath($sNewFilePath, false),
            'width' => $iWidth,
            'height' => $iHeight,
            'koef' => $oImage->getSrcHeight() / self::cropHeight,
        ];
    }

    /**
     * Заменяем миниатюры в изображении $iImageId.
     *
     * @param $iImageId
     * @param $mProfile
     * @param $mThumbnailPath
     *
     * @return bool|int
     */
    public static function replaceImageReCropFormat($iImageId, $mProfile, $mThumbnailPath = false)
    {
        if (!$aImage = gallery\Photo::getImage($iImageId)) {
            return false;
        }

        $aImagesData = json_decode($aImage['images_data'], true);

        foreach ($mProfile as $sFormatName => $aFormat) {
            // удаление старых(земененных) изображений
            Files::remove(WEBPATH . $aImagesData[$sFormatName]['file']);

            // замена миниатюры на новую
            $aImagesData[$sFormatName] = $aFormat;
        }

        $aData['images_data'] = json_encode($aImagesData);
        // изменяем миниатюры для админки
        if ($mThumbnailPath) {
            /*
             * удаление старой миниатюры
             */
            Files::remove(ROOTPATH . $aImage['thumbnail']);
            $aData['thumbnail'] = $mThumbnailPath;
        }

        return gallery\Photo::setImage($aData, $iImageId);
    }

    /**
     * Валидирует набор id для альбома.
     *
     * @static
     *
     * @param $aIdList
     * @param $iAlbumId
     *
     * @return array
     */
    public static function validateIdList($aIdList, $iAlbumId)
    {
        // набор приведенных к типу id на удаление
        $aIntIdList = [];
        foreach ($aIdList as $mId) {
            if ($iId = (int) $mId) {
                $aIntIdList[] = $iId;
            }
        }

        // набор точно присутствующих в альбоме id
        $aValidIdList = [];

        // изображения в альбоме
        if ($aAlbumItems = gallery\Photo::getFromAlbum($iAlbumId, false)) {
            foreach ($aAlbumItems as $aItem) {
                if (in_array($aItem['id'], $aIntIdList)) {
                    $aValidIdList[] = $aItem['id'];
                }
            }
        }

        return $aValidIdList;
    }

    /**
     * Валидирует набор id альбомов для раздела.
     *
     * @static
     *
     * @param array $aIdList
     * @param int $iSectionId
     *
     * @return array
     */
    public static function validateIdAlbumsList($aIdList, $iSectionId)
    {
        // набор приведенных к типу id на удаление
        $aIntIdList = [];
        foreach ($aIdList as $mId) {
            if ($iId = (int) $mId) {
                $aIntIdList[] = $iId;
            }
        }

        // набор точно присутствующих в разделе id альбомов
        $aValidIdList = [];

        if ($aAlbumItems = gallery\Album::getBySection($iSectionId, false)) { // альбомы в разделе
            foreach ($aAlbumItems as $aItem) {
                if (in_array($aItem['id'], $aIntIdList)) {
                    $aValidIdList[] = $aItem['id'];
                }
            }
        }

        return $aValidIdList;
    }

    public static function Rotate($aFormat, $aImg)
    {
        if (!$aFormat[0]['resize_on_larger_side']) {
            return false;
        }
        if ((($aImg['width'] / $aImg['height'] < 1) and ($aFormat['0']['width'] / $aFormat['0']['height'] > 1)) or
            (($aImg['width'] / $aImg['height'] > 1) and ($aFormat['0']['width'] / $aFormat['0']['height'] < 1))) {
            return true;
        }

        return false;
    }

    /**
     * Создание директории для временных фото.
     */
    public static function createTempDir()
    {
        if (!file_exists(ROOTPATH . 'web/' . self::$sTempPath)) {
            mkdir(ROOTPATH . 'web/' . self::$sTempPath);
            chmod(ROOTPATH . 'web/' . self::$sTempPath, 0755);
        }
    }

    /**
     * Получить конфиг библиотеки JustifiedGallery для раздела.
     *
     * @param $iSectionId - ид раздела
     * @param bool $bWithPreffix - добавлять к полям преффикс?
     *
     * @return array
     */
    public static function getConfigJustifiedGallery($iSectionId, $bWithPreffix = false)
    {
        $sConfig = Parameters::getValByName($iSectionId, 'content', 'justifiedGalleryConfig', true);
        $aConfig = json_decode($sConfig, true);
        $aOut = [];

        if ($bWithPreffix) {
            foreach ($aConfig as $sKey => $mValue) {
                $aOut['justifiedGalleryOption_' . $sKey] = $mValue;
            }
        } else {
            $aOut = $aConfig;
        }

        return $aOut;
    }

    public static function getTransitionEffectFancyBox()
    {
        return $aType = [
            'disable' => \Yii::t('gallery', 'effect_fanctbox_disable'),
            'fade' => \Yii::t('gallery', 'effect_fanctbox_fade'),
            'slide' => \Yii::t('gallery', 'effect_fanctbox_slide'),
            'circular' => \Yii::t('gallery', 'effect_fanctbox_circular'),
            'tube' => \Yii::t('gallery', 'effect_fanctbox_tube'),
            'zoom-in-out' => \Yii::t('gallery', 'effect_fanctbox_zoom'),
            'rotate' => \Yii::t('gallery', 'effect_fanctbox_rotate'), ];
    }

    /**
     * Информация форматов изображения для шаблона "Строка".
     *
     * @param $oPhoto gallery\models\Photos - изображение
     *
     * @return array
     */
    public static function getFormats4GalleryTileByPhoto(gallery\models\Photos $oPhoto)
    {
        $aOut = [];

        /** @var array $aImagesData */
        $aImagesData = $oPhoto->images_data;

        if (isset($aImagesData['preview_ver'])) {
            $aOut[] = $aImagesData['preview_ver'];
        }

        if (isset($aImagesData['preview_hor'])) {
            $aOut[] = $aImagesData['preview_hor'];
        }

        return $aOut;
    }
}
