<?php

namespace skewer\components\gallery;

use skewer\base\ui;
use skewer\build\Adm\Gallery\Api;
use skewer\build\Adm\Gallery\Seo;
use skewer\components\design\Design;
use skewer\components\gallery\models\Albums;
use skewer\components\gallery\models\Photos;
use skewer\helpers\Files;
use skewer\helpers\Image;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Api для работы с изображениями галереи
 * Class Photo.
 */
class Photo
{
    /**
     * Название директории для хранения thumbnails.
     *
     * @var string
     */
    protected static $sThumbnailDirectory = 'thumbnails';

    /**
     * Ширина thumbnails для списка изображений в альбоме.
     *
     * @var int
     */
    protected static $iThumbnailWidth = 150;

    /**
     * Высота thumbnails для списка изображений в альбоме.
     *
     * @var int
     */
    protected static $iThumbnailHeight = 150;

    /**
     * Возвращает список изображений для альбома/альбомов $mAlbumId. Если данные не найдены вернется false;.
     *
     * @static
     *
     * @param array|int $mAlbumId Альбом или массив альбомов, которые в случае с $bWithoutHidden = true будут проверяться на видимость
     * @param bool $bWithoutHidden Указатель на необходимость выборки без учёта видимости
     * @param int $iOnPage - Число выбираемых изображений. 0 - все
     * @param int $iPage - номер страницы
     * @param int $iTotalCount - общее количество записей, удолетвовяющих условию
     *
     * @return bool|Photos[]
     */
    public static function getFromAlbum($mAlbumId, $bWithoutHidden = false, $iOnPage = 0, $iPage = 1, &$iTotalCount = 0)
    {
        if (!$mAlbumId or (!is_array($mAlbumId) and (!$mAlbumId = (int) $mAlbumId))) {
            return false;
        }
        $query = models\Photos::find();
        $query->where(['album_id' => $mAlbumId]);
        if ($bWithoutHidden) {
            $query->andWhere(['visible' => 1]);
        }

        //вывод изображений из альбомов зависит от порядка альбомов в админке
        /** @var models\Photos [] $aItems */
        $sOrderAlbum = (is_array($mAlbumId)) ? 'FIELD(album_id,' . implode(', ', array_values($mAlbumId)) . ')' : '';
        if ($sOrderAlbum) {
            $query->orderBy([new \yii\db\Expression($sOrderAlbum)]);
        }

        $query->addOrderBy('priority DESC');

        $iTotalCount = (int) $query->count();

        if ($iOnPage) {
            $query
                ->limit($iOnPage)
                ->offset(($iPage - 1) * $iOnPage);
        }

        /** @var Photos[] $aItems */
        $aItems = $query->all();

        if (!$aItems) {
            return false;
        }

        foreach ($aItems as $oItem) {
            $oItem->images_data = $oItem->getPictures();
        }

        return $aItems;
    }

    /**
     * Возвращает данные записи изображения $iImageId.
     *
     * @static
     *
     * @param int $iImageId Id изображения
     *
     * @return bool|models\Photos Возвращает массив данных изображения либо false в случае отсутствия записи
     */
    public static function getImage($iImageId)
    {
        /** @var models\Photos $Image */
        if ((!$iImageId = (int) $iImageId) or
             (!$Image = models\Photos::findOne($iImageId))) {
            return false;
        }

        return $Image;
    }

    /**
     * Добавляет либо обновляет данные изображения в зависимости от набора параметров.
     *
     * @static
     *
     * @param array $aData Данные
     * @param int $iImageId Id Обновляемого изображения
     *
     * @throws \Exception|UserException Сообщение об ошибки валидации полей
     *
     * @return bool|int id созданной записи или \Exception / false
     */
    public static function setImage(array $aData, $iImageId = 0)
    {
        if ($iImageId) { // Изменение изображения с валидацией полей
            if (!$oPhoto = models\Photos::findOne($iImageId)) {
                throw new \Exception(\Yii::t('gallery', 'general_field_empty'));
            }
        } else {// Вставка нового изображения
            $oPhoto = new models\Photos();
            if (isset($aData['album_id']) and $aData['album_id']) {
                $aData['priority'] = models\Photos::find()
                    ->where(['album_id' => $aData['album_id']])
                    ->max('priority') + 1;
            }
        }

        $oPhoto->setAttributes($aData);
        if ($oPhoto->save(true)) {
            return $oPhoto->id;
        }

        if ($oPhoto->hasErrors()) { // Если возникла ошибка валидации, то выбросить исключение
            $sFirstError = ArrayHelper::getColumn($oPhoto->errors, '0', false)[0];
            throw new UserException($sFirstError);
        }

        return false;
    }

    /**
     * Возвращает количество видимых Изображений в альбоме $iAlbumId.
     *
     * @static
     *
     * @param int $iAlbumId Id Альбома
     * @param bool $onlyVisible Только видимые?
     *
     * @return int
     */
    public static function getCountByAlbum($iAlbumId, $onlyVisible = true)
    {
        return models\Photos::find()
            ->where(['album_id' => $iAlbumId] + (($onlyVisible) ? ['visible' => 1] : []))
            ->count('id');
    }

    /**
     * Удаляет изображение $iImageId из альбома.
     *
     * @static
     *
     * @param int $iImageId Id удаляемого изображения
     * @param bool|string $mError Переменная, возвращающая сообщение об ошибке случае неудачи
     *
     * @throws \Exception
     *
     * @return bool Возвращает true в случае удачного удаление записи и файлов либо false и сообщение об
     * ошибке в параметре $sError
     */
    public static function removeImage($iImageId, &$mError = '')
    {
        try {
            if (!$iImageId = (int) $iImageId) {
                throw new \Exception(\Yii::t('gallery', 'photos_error_notfound', ['']));
            }
            /* Запросить запись */
            /* @var models\Photos $Image */
            if (!$oPhoto = models\Photos::findOne($iImageId)) {
                throw new \Exception(\Yii::t('gallery', 'photos_error_notfound', ['']));
            }
            /* Удалить исходник */
            Files::remove(WEBPATH . $oPhoto->source);

            /* Удалить thumbnail */
            Files::remove(WEBPATH . $oPhoto->thumbnail);

            /* Удалить ресайзы */
            foreach ($oPhoto->getPictures() as $pic) {
                Files::remove(WEBPATH . $pic['file']);
            }

            /* Удалить запись */
            $oPhoto->delete();

            /* Пересчитать зависимости */
        } catch (\Exception $e) {
            $mError = $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Изменение видимости изображения.
     *
     * @param $iImageId
     *
     * @return bool
     */
    public static function toggleActivePhoto($iImageId)
    {
        $oPhoto = models\Photos::findOne($iImageId);
        if (!$oPhoto) {
            return false;
        }

        $oPhoto->visible = (int) !$oPhoto->visible;

        return $oPhoto->save();
    }

    public static function hexToRgb($color)
    {
        if (mb_strpos($color, 'rgba') !== false) {
            $aOut = [];

            $color = str_replace('rgba(', '', $color);
            $color = str_replace(')', '', $color);

            list($aOut['red'], $aOut['green'], $aOut['blue'], $aOut['trans']) = explode(',', $color);

            return $aOut;
        }

        if (mb_strpos($color, 'rgb') !== false) {
            $aOut = [];

            $color = str_replace('rgb(', '', $color);
            $color = str_replace(')', '', $color);

            list($aOut['red'], $aOut['green'], $aOut['blue']) = explode(',', $color);

            return $aOut;
        }

        // проверяем наличие # в начале, если есть, то отрезаем ее
        if (isset($color[0]) and $color[0] == '#') {
            $color = mb_substr($color, 1);
        }

        // разбираем строку на массив
        if (mb_strlen($color) == 6) { // если hex цвет в полной форме - 6 символов
            list($red, $green, $blue) = [
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5],
            ];
        } elseif (mb_strlen($color) == 3) { // если hex цвет в сокращенной форме - 3 символа
            list($red, $green, $blue) = [
                $color[0] . $color[0],
                $color[1] . $color[1],
                $color[2] . $color[2],
            ];
        } else {
            $red = '0';
            $green = '0';
            $blue = '0';
        }

        // переводим шестнадцатиричные числа в десятичные
        $red = hexdec($red);
        $green = hexdec($green);
        $blue = hexdec($blue);

        // вернем результат
        return [
            'red' => $red,
            'green' => $green,
            'blue' => $blue,
        ];
    }

    /**
     * Обрабатывает изображение $sImagePath согласно профилю настроек $iProfileId для раздела $iSectionId.
     *
     * @static
     *
     * @param string $sImageFile Абсолютный путь к исходному файлу изображения
     * @param array|int $mProfileId Id профиля настроек, согласно которому будет обработано изображение
     * @param int $iAlbumId Id альбома галереи
     * @param bool $bProtected Указатель на необходимость сохранять файлы в закрытую для доступа директорию
     * @param bool $bCreateAllFormat Флаг определяет создавать ли миниатюры всех доступных форматов (false - только пришедших в $mProfileId)
     * @param bool|string $mError Переменная, в которую будет возвращен текст ошибки либо false
     * @param int $cropHeight Параметр обрезки по высоте (по старому берётся из \skewer\build\Adm\Gallery\Api::cropHeight)
     * @param mixed $bLockAccomodiate
     * @param mixed $bRotate
     * @param null|mixed $aFormats
     *
     * @throws \Exception
     *
     * @return array|bool Возвращает массив с описанием созданных изображений либо false
     */
    public static function processImage($sImageFile, $mProfileId, $iAlbumId, $bProtected = false, $bCreateAllFormat = true, &$mError = false, $cropHeight = 400, $bLockAccomodiate = false, $bRotate = false, $aFormats = null)
    {
        /** Закэшированый список типов профилей для пакетной обработки */
        static $aProfilesTypes = [];

        try {
            if (is_array($mProfileId)) {
                $iProfileId = $mProfileId['iProfileId'];
                $aCropData = $mProfileId['crop'];
            } else {
                $iProfileId = (int) $mProfileId;
                $aCropData = [];
            }

            if (!isset($aProfilesTypes[$iProfileId])) {
                $aProfile = Profile::getById($iProfileId);
                $aProfilesTypes[$iProfileId] = ($aProfile) ? $aProfile['type'] : '';
            }

            /* Обработали ошибки входных данных */
            if (!(int) $iProfileId) {
                throw new \Exception(\Yii::t('gallery', 'profile_error_imgprocc'));
            }
            if (!(int) $iAlbumId) {
                throw new \Exception(\Yii::t('gallery', 'section_error_imgprocc'));
            }
            if (!file_exists($sImageFile)) {
                throw new \Exception(\Yii::t('gallery', 'photos_error_imgprocc', [$sImageFile]));
            }
            /* Путь к корневой директории галереи в текущем разделе */
            $sImagePath = Api::getAlbumDir($iAlbumId) . \DIRECTORY_SEPARATOR;

            $aProfile = Profile::getById($iProfileId);

            if ($aFormats === null) {
                /* Чтение настроек профиля */
                $aFormats = Format::getByProfile($iProfileId);
            }

            if (!count($aFormats)) {
                throw new \Exception(\Yii::t('gallery', 'format_error_imgprocc'));
            }
            /* Загрузка исходного изображения для дальнейшей обработки */
            $aValues = \skewer\build\Adm\Files\Api::getLanguage4Image();
            Image::loadErrorMessages($aValues);
            $oImage = new Image();

            $aOut = false;
            if (!$oImage->load($sImageFile)) {
                throw new \Exception(\Yii::t('gallery', 'photos_error_imgload'));
            }
            $oImage->saveToBuffer();

            $oImage->loadFromBuffer();

            /* Создание thumbnail для системы администрирования */
            $bCreatedThumbnail = false;

            $aThumbnail = [
                'width' => self::$iThumbnailWidth,
                'height' => self::$iThumbnailHeight,
            ];

            //предположительно используется для кропа обложек альбома
            //см. комментарий в задаче 76461
            foreach ($aFormats as $aFormat) {
                if ($aFormat['name'] == 'thumbnail_overlay') {
                    $aThumbnail = [
                        'width' => $aFormat['width'],
                        'height' => $aFormat['height'],
                    ];
                }
            }

            //однозначного назначения этого кода нет
            //см. комментарий в задаче 76461
            if (isset($aFormats)) {
                foreach ($aFormats as $aFormat) {
                    if ($aFormat['name'] == 'preview' and isset($aCropData['preview'])) {
                        // обрезка
                        $oImage->cropImage(
                            $aThumbnail['width'],
                            $aThumbnail['height'],
                            false,
                            true
                        );

                        $bCreatedThumbnail = true;
                    }
                }
            }

            if (!$bCreatedThumbnail) {
                $oImage->resize($aThumbnail['width'], $aThumbnail['height']);
            }

            $sSavedFile = Files::generateUniqFileName($sImagePath . self::$sThumbnailDirectory . \DIRECTORY_SEPARATOR, $sImageFile);
            $sSavedFile = str_replace(Files::getRootUploadPath($bProtected), '', $sSavedFile);
            $sDir = Files::createFolderPath(dirname($sSavedFile), $bProtected);
            if (!$sDir) {
                throw new \Exception(\Yii::t('gallery', 'photos_error_thumbnail'));
            }
            $sThumbnailPath = $sDir . \DIRECTORY_SEPARATOR . basename($sSavedFile);

            $sThumbnailPath = $oImage->save($sThumbnailPath); // Сохранить измененное thumbnail
            $aOut['thumbnail'] = Files::getWebPath($sThumbnailPath, false);

            /* Обработка изображения по каждому из форматов профиля */
            foreach ($aFormats as &$aFormat) {
                $oImage->loadFromBuffer();

                if (isset($aCropData[$aFormat['name']])) {
                    if ($aFormat['scale_and_crop'] == '-1') {
                        $aFormat['scale_and_crop'] = 0;
                    }

                    if ($bLockAccomodiate) {
                        $aFormat['scale_and_crop'] = 0;
                        $aFormat['resize_on_larger_side'] = 0;
                    }

                    /*Если по ширине исходник меньше чем формат. включим вписывание*/
                    if ($aFormat['width'] >= $oImage->getSrcWidth()) {
                        $aFormat['scale_and_crop'] = 1;
                    }

                    /*Если по высоте исходник меньше чем формат. включим вписывание*/
                    if ($aFormat['height'] >= $oImage->getSrcHeight()) {
                        $aFormat['scale_and_crop'] = 1;
                    }

                    /*Если хоть 1 из параметров динамический. включим вписывание*/
                    if ($aFormat['width'] == 0 || $aFormat['height'] == 0) {
                        $aFormat['scale_and_crop'] = 1;

                        list($iWidth, $iHeight) = $oImage->getSize();

                        $aOperated = Image::getOperatedSizes($aFormat['width'], $aFormat['height'], $iWidth, $iHeight);
                        $aFormat['width'] = $aOperated['width'];
                        $aFormat['height'] = $aOperated['height'];
                    }

                    if ($aFormat['scale_and_crop']) {
                        $iTmpWidth = $aFormat['width'];
                        $iTmpHeight = $aFormat['height'];
                    } else {
                        $bRotate = Image::needRotation($aFormat['width'], $aFormat['height'], $oImage->getSrcWidth(), $oImage->getSrcHeight(), $aFormat['resize_on_larger_side']);
                        $aNeedParams = $oImage->getNotScaleParams($aFormat['width'], $aFormat['height'], $aFormat['scale_and_crop']);

                        $iTmpWidth = $aNeedParams['width'];
                        $iTmpHeight = $aNeedParams['height'];
//                        $iTmpWidth = 640;
//                        $iTmpHeight = 410;
                    }

                    $oImage->cropImage( // обрезка
                        $iTmpWidth,
                        $iTmpHeight,
                        $aFormat['resize_on_larger_side'],
                        $aFormat['scale_and_crop']
                    );

                    if ($bRotate) {
                        $tmp = $iTmpWidth;
                        $iTmpWidth = $iTmpHeight;
                        $iTmpHeight = $tmp;
                        $tmp = $aFormat['width'];
                        $aFormat['width'] = $aFormat['height'];
                        $aFormat['height'] = $tmp;
                    }

                    if ($iTmpWidth > $aFormat['width'] || $iTmpHeight > $aFormat['height']) {
                        $oImage->cropToSize($iTmpWidth, $iTmpHeight, $aFormat['width'], $aFormat['height']);
                    }
                } else {
                    if (!$bCreateAllFormat) {
                        continue;
                    }
                    // обычное изменение размера
                    $oImage->resize($aFormat['width'], $aFormat['height'], $aFormat['resize_on_larger_side'], $aFormat['scale_and_crop']);
                }

                list($iWidth, $iHeight) = $oImage->getSize();
                $oImage->updSizes($iWidth, $iHeight);
                /* not arbeiten */
                if ($aFormat['use_watermark']) {
                    $oImage->applyWatermark($aFormat['watermark'], self::hexToRgb($aProfile['watermark_color']), $aFormat['watermark_align']);
                }

                $sSavedFile = Files::generateUniqFileName($sImagePath . $aFormat['name'] . \DIRECTORY_SEPARATOR, basename($sImageFile));
                $sSavedFile = str_replace(Files::getRootUploadPath($bProtected), '', $sSavedFile);
                $sDir = Files::createFolderPath(dirname($sSavedFile), $bProtected);
                if (!$sDir) {
                    throw new \Exception(\Yii::t('gallery', 'directory_error_imgprocc'));
                }
                $sDir = rtrim($sDir, \DIRECTORY_SEPARATOR); // Защита от второго слеша
                $sNewFilePath = $sDir . \DIRECTORY_SEPARATOR . basename($sSavedFile);

                /* Сохранить измененное изображение */
                $sNewFilePath = $oImage->save($sNewFilePath);
                if (!$sNewFilePath) {
                    throw new \Exception(\Yii::t('gallery', 'photos_error_imgsave'));
                }
                list($iWidth, $iHeight) = $oImage->getSize();
                $aImage = [
                    'file' => Files::getWebPath($sNewFilePath, false),
                    'name' => $aFormat['name'],
                    'width' => $iWidth,
                    'height' => $iHeight,
                ];
                $aOut[$aFormat['name']] = $aImage;
            }// each format

            $oImage->clear();
        } catch (\Exception $e) {
            $mError = $e->getMessage();

            return false;
        }

        return $aOut;
    }

    // func

    /**
     * Сортирует объекты списка.
     *
     * @param int $iItemId id перемещаемого объекта
     * @param int $iTargetId id объекта, относительно которого идет перемещение
     * @param string $sOrderType направление переноса
     *
     * @return bool
     */
    public static function sortImages($iItemId, $iTargetId, $sOrderType = 'before')
    {
        // Здесь обратная сортировка
        $sOrderType = ($sOrderType == 'before') ? 'after' : 'before';

        $bRes = ui\Api::sortObjects($iItemId, $iTargetId, models\Photos::className(), $sOrderType, 'album_id');

        //Обновление даты модификации альбома

        $Obj = self::getImage($iItemId);
        if ($Obj) {
            /** @var Albums $oAlbum */
            if ($oAlbum = Albums::findOne($Obj->album_id)) {
                $oAlbum->last_modified_date = date('Y-m-d H:i:s', time());
                $oAlbum->save();
            }
        }

        return $bRes;
    }

    /**
     * Получить несколько последних фотографий, имеющих исходное изображение, из всех фотогалерей
     * (метод используется в сервисе чистки исходных изображений).
     *
     * @param int $iLastDays Число прошедших дней
     * @param int $iLimit Ограничение на количество
     *
     * @return array
     */
    public static function getOlderPhotoWithSourse($iLimit, $iLastDays = 7)
    {
        return models\Photos::findBySql('SELECT * FROM `' . models\Photos::tableName() . "` WHERE `source` != '' AND `creation_date` < '" . date('Y-m-d H:i:s', strtotime("-{$iLastDays} days")) . "' LIMIT 0, {$iLimit}")->asArray()->all();
    }

    /**
     * Добавление фотки в альбом
     *
     * @param $sPhoto
     * @param $iAlbumId
     * @param $crop
     * @param $iProfileId
     *
     * @throws \Exception
     *
     * @return bool|int
     */
    public static function addPhotoInAlbum($sPhoto, $iAlbumId, $crop, $iProfileId)
    {
        $sTitle = '';
        $sAltTitle = '';
        $sDescription = '';

        // построим пути
        $sImagePath = mb_substr($sPhoto, mb_strrpos(\DIRECTORY_SEPARATOR, $sPhoto) + 1);
        $sImageFullPath = $sPhoto;

        $mProfileId = [
            'crop' => $crop,
            'iProfileId' => $iProfileId,
        ];
        $aNewImage = self::processImage($sImageFullPath, $mProfileId, $iAlbumId, false, true, $sError);
        if (!$aNewImage or $sError) {
            throw new \Exception($sError);
        }
        $sThumbnail = (isset($aNewImage['thumbnail'])) ? $aNewImage['thumbnail'] : '';
        unset($aNewImage['thumbnail']);
        /*переносим исходники*/
        if (!file_exists(WEBPATH . 'files/import_sources')) {
            mkdir(WEBPATH . 'files/import_sources');
        }

        $aImgInfo = pathinfo($sImageFullPath);

        $sFileName = WEBPATH . 'files/import_sources/' . time() . '_' . random_int(0, 10000) . '.' . $aImgInfo['extension'];
        copy($sImageFullPath, $sFileName);

        /* Сохранение сущности в БД */

        return self::setImage([
            'title' => $sTitle,
            'alt_title' => $sAltTitle,
            'source' => str_replace(WEBPATH, '/', $sFileName),
            'visible' => 1,
            'album_id' => $iAlbumId,
            'thumbnail' => $sThumbnail,
            'description' => $sDescription,
            'images_data' => json_encode($aNewImage),
        ]);
    }

    /**
     * Удаление фотографий альбома.
     *
     * @param $iAlbumId
     *
     * @throws \Exception
     */
    public static function removeFromAlbum($iAlbumId)
    {
        /* Выбрать изображения к нему */
        if ($Images = self::getFromAlbum($iAlbumId)) {
            /* Удалить изображения */
            foreach ($Images as $Image) {
                $mError = false;
                if (!self::removeImage($Image['id'], $mError)) {
                    throw new \Exception($mError);
                }
            }// each image in current album
        }
    }

    /**
     * Получить информацию о файле логотипа сайта.
     *
     * @return array|bool
     */
    public static function getLogoInfo()
    {
        $sLogo = Design::getLogo();
        if ($aLogoSize = @getimagesize(WEBPATH . $sLogo)) {
            return [
                'src' => $sLogo,
                'width' => $aLogoSize[0],
                'height' => $aLogoSize[1],
            ];
        }

        return false;
    }

    /**
     * Возвращает список изображений для альбома/альбомов $mAlbumId с seo данными. Если данные не найдены вернется false;.
     *
     * @param array|int $mAlbumId Альбом или массив альбомов, которые в случае с $bWithoutHidden = true будут проверяться на видимость
     * @param int $iSectionId - Текущий раздел
     * @param bool $bWithoutHidden - Указатель на необходимость выборки без учёта видимости
     * @param int $iPage - номер страницы
     * @param int $iOnPage - количество записей на странице
     * @param int $iTotalCount - общее количество записей, удолетвовяющих условию
     *
     * @return bool|models\Photos[]
     */
    public static function getListWithSeoData($mAlbumId, $iSectionId, $bWithoutHidden = false, $iOnPage = 0, $iPage = 1, &$iTotalCount = 0)
    {
        $aPhotos = self::getFromAlbum($mAlbumId, $bWithoutHidden, $iOnPage, $iPage, $iTotalCount);

        if (!$aPhotos) {
            return [];
        }

        //Фото, сгруппированные по альбомам
        $aPhotosByAlbum = ArrayHelper::map($aPhotos, 'id', 'priority', 'album_id');

        $aPhotos = ArrayHelper::index($aPhotos, 'id');

        foreach ($aPhotosByAlbum as $iAlbumId => $aPhotoInAlbum) {
            $oSeo = new Seo($iAlbumId, $iSectionId);
            $oSeo->loadDataEntity();

            foreach ($aPhotoInAlbum as $iId => $iPriority) {
                if (isset($aPhotos[$iId]) && (!$aPhotos[$iId]->alt_title)) {
                    $aPhotos[$iId]->alt_title = $oSeo->parseField('altTitle', ['sectionId' => $iSectionId, 'label_number_photo' => $iPriority]);
                    $oSeo->clearLabelsFromEntity();
                }
            }
        }

        return $aPhotos;
    }

    /**
     * Выбор изображения для выполнения рекропа.
     * Если есть исходник, то выбирается он,
     * иначе выбирается наибольшое по размеру изображение.
     *
     * @param int $iPhotoId - id изображения
     *
     * @return string | false - путь к изображению выбранного формата, false - в случае ошибки
     */
    public static function getMaxFormatByPhotoId($iPhotoId)
    {
        $aImage = self::getImage($iPhotoId);

        // Если есть исходник, то берём его
        if (!empty($aImage['source']) && file_exists(WEBPATH . trim($aImage['source'], '/'))) {
            return $aImage['source'];
        }

        $aImagesData = json_decode($aImage['images_data'], true);

        // Ищём наибольший формат
        $aFormatNameToSize = [];

        foreach ($aImagesData as $sFormatName => $aFormatConfig) {
            if (!empty($aFormatConfig['file']) && file_exists(WEBPATH . trim($aFormatConfig['file'], '/'))) {
                $iSizeSum = $aFormatConfig['width'] + $aFormatConfig['height'];
                $aFormatNameToSize[$sFormatName] = $iSizeSum;
            }
        }

        if (!$aFormatNameToSize) {
            return false;
        }

        $iMaxSize = max($aFormatNameToSize);

        $sMaxKey = array_search($iMaxSize, $aFormatNameToSize);

        if ($sMaxKey === false) {
            return false;
        }

        return $aImagesData[$sMaxKey]['file'];
    }
}
