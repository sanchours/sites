<?php

namespace skewer\components\import\field;

use skewer\base\SysVar;
use skewer\components\gallery\Album;
use skewer\components\gallery\Format;
use skewer\components\gallery\Photo;
use skewer\components\gallery\Profile;

/**
 * Обработчик поля типа галерея.
 */
class Gallery extends Prototype
{
    /** @var string Разделитель фоток */
    protected $delimiter = ',';

    /** @var bool Пересоздавать фото в альбоме */
    protected $recreate = false;

    /** @var bool Искать по названию */
    protected $find = false;

    /** @var [] кроп */
    protected $crop = [];

    /** @var int Профиль */
    protected $profile = 0;

    /** @var array Изображения */
    protected $photos = [];

    /** @var int Альбом */
    protected $album = 0;

    /** @var array Разрешенные форматы файлов */
    protected $allowFormatFile = [];

    const ImageDir = 'gallery/';

    protected static $parameters = [
        'delimiter' => [
            'title' => 'field_gallery_delimiter',
            'datatype' => 's',
            'viewtype' => 'string',
            'default' => ',',
        ],
        'find' => [
            'title' => 'field_gallery_find',
            'datatype' => 'i',
            'viewtype' => 'check',
            'default' => '0',
        ],
        'recreate' => [
            'title' => 'field_gallery_recreate',
            'datatype' => 's',
            'viewtype' => 'check',
            'default' => '0',
        ],
    ];

    public function init()
    {
        $this->profile = Profile::getDefaultId(Profile::TYPE_CATALOG);

        $this->crop = Format::getCrop4Catalog();

        $this->allowFormatFile = SysVar::get('import_upload_images');
        $this->allowFormatFile = explode(', ', $this->allowFormatFile);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        $this->photos = [];
    }

    /**
     * Отдает значение на сохранение в запись товара.
     *
     * @return mixed
     */
    public function getValue()
    {
        $this->album = $this->getGoodsRow()->getData()[$this->fieldName];

        //если не нужна перезапись и фотки уже есть
        if (!$this->recreate && Photo::getCountByAlbum($this->album)) {
            return $this->album;
        }

        $sVal = implode($this->delimiter, $this->values);

        if ($sVal) {
            //создадим альбом
            if (!$this->album) {
                $this->album = Album::create4Catalog();
            }

            //собираем фотки
            if ($this->find) {
                $aFiles = [];

                foreach (explode($this->delimiter, $sVal) as $sFileName) {
                    //ищем по названию
                    $aFilesChunk = glob(IMPORT_FILEPATH . self::ImageDir . $sFileName . '.*');

                    if (is_array($aFilesChunk) && count($aFilesChunk) > 0) {
                        array_push($aFiles, ...$aFilesChunk);
                    }
                }

                if ($aFiles) {
                    //проверим на форматы
                    foreach ($aFiles as $sFileName) {
                        $ext = mb_strtolower(mb_substr($sFileName, mb_strrpos($sFileName, '.') + 1));
                        if (in_array($ext, $this->allowFormatFile)) {
                            $this->photos[] = $sFileName;
                        }
                    }
                }
            } else {
                $this->photos = explode($this->delimiter, $sVal);
                $formatErrors = [];
                foreach ($this->photos as $key => $photo) {
                    $photoFormat = pathinfo($photo, PATHINFO_EXTENSION);
                    if (in_array($photoFormat, $this->allowFormatFile)) {
                        $photo = IMPORT_FILEPATH . self::ImageDir . $photo;
                        continue;
                    }
                    $formatErrors[] = \Yii::t('gallery', 'photos_error_invalid_format', [$photo]);
                    unset($this->photos[$key]);
                }
                if ($formatErrors) {
                    $formatErrors = implode('; ', $formatErrors);
                    throw new \Exception($formatErrors);
                }
            }
        }

        return $this->album;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $oGoodsRow = $this->getGoodsRow();
        if (!$oGoodsRow) {
            return;
        }

        /* Добавляем фотки здесь, так как при добавлении товар уже должен быть создан */
        if ($this->photos) {
            //чистим старые
            Photo::removeFromAlbum($this->album);
            foreach ($this->photos as $sPhoto) {
                $bAbb = Photo::addPhotoInAlbum($sPhoto, $this->album, $this->crop, $this->profile);
                if ($bAbb) {
                    $this->logger->incParam('add_photo');
                }
            }
        }
    }
}
