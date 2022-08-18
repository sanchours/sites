<?php

namespace skewer\build\Adm\Gallery;

use skewer\base\section\Parameters;
use skewer\base\site\Site;
use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Tool\Utils\Api as UtilsApi;
use skewer\components\catalog\Section;
use skewer\components\gallery;
use skewer\components\gallery\Profile;
use skewer\components\seo;
use skewer\helpers\Files;
use skewer\helpers\Image;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Система администрирования для модуля фотогаллереи.
 */
class Module extends Adm\Tree\ModulePrototype
{
    /**
     * Количество записей на страницу.
     *
     * @var int
     */
    protected $iOnPage = 10;

    /**
     * Массив полей, выводимых колонками в списке альбомов.
     *
     * @var array
     */
    protected $aAlbumsListFields = ['id', 'title', 'visible'];

    /**
     * Номер текущей страницы.
     *
     * @var int
     */
    protected $iPage = 0;

    /**
     * Id текущего альбома.
     *
     * @var int
     */
    protected $iCurrentAlbumId = 0;
    /**
     * Сообщение об ошибке, если возникла.
     *
     * @var string
     */
    protected $sErrorText = '';

    /**
     * Максимально допустимый размер для загружаемых изображений.
     *
     * @var int
     */
    protected $iMaxUploadSize = 0;

    /**
     * Название, присваиваемое загруженному изображению по-умолчанию.
     *
     * @var string
     */
    protected $sDefaultImageTitle = 'Gallery.image';

    /*
     * Массив данных для ресайза
     * заполняется при загрузке файла
     */
    protected $aUploadedData = [];

    /** @var bool Выводить только интерфейс редактированья фотографий */
    protected $onlyAlbumEditor = false;

    /** @var bool Создавать альбом */
    protected $createAlbum = false;

    /** @var bool Флаг всплывающего окна */
    protected $popup = false;

    /** @var string $sSeoClass Класс seo-компонента */
    protected $sSeoClass = '';

    /** @var int $iEntityId - id сущности, к которой принадлежит галлерея */
    protected $iEntityId = 0;

    /* Methods */

    /**
     * Иницализация.
     */
    protected function preExecute()
    {
        /* текущая страница постраничного */
        $this->iPage = $this->getInt('page');

        /* Максимально допустимый размер для загружаемых изображений */
        $this->iMaxUploadSize = \Yii::$app->getParam(['upload', 'maxsize']);

        /* Восстанавливаем текущий альбом (не нужно перекрывать если задан в подпроцессе) */
        if (!$this->iCurrentAlbumId) {
            $this->iCurrentAlbumId = $this->getInt('currentAlbumId');
        }

        if (!$this->sSeoClass) {
            $this->sSeoClass = \skewer\build\Adm\Gallery\Seo::className();
        }

        if (!$this->iEntityId) {
            $this->iEntityId = $this->iCurrentAlbumId;
        }
    }

    // func

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            'page' => $this->iPage,
            'currentAlbumId' => $this->iCurrentAlbumId,
            'url' => '/oldadmin/?mode=galleryBrowser&sectionId=' . $this->sectionId()
        ]);
    }

    // func

    /**
     * Вызывается в случае отсутствия явного обработчика.
     *
     * @return int
     */
    protected function actionInit()
    {
        if (Site::isNewAdmin()) {
            return $this->actionShowIframe();
        }

        // сборщик мусора
        if (TmpModule::allowStartScavenger()) {
            $this->startScavenger();
        }

        $this->setModuleLangValues(
            [
                'galleryNoAlbums',
                'galleryNoImages',
                'galleryNoItems',
                'galleryDeleteAlbum',
                'galleryDeleteAlbums',
                'galleryDeleteMeasure',
                'galleryDeleteConfirm',
                'galleryUploadingImage',
            ]
        );

        if ($this->sErrorText) {
            return $this->showError();
        }
        if ($this->onlyAlbumEditor && $this->iCurrentAlbumId) {
            return $this->actionShowAlbum();
        }
        if ($this->onlyAlbumEditor && $this->createAlbum) {
            return $this->actionNonAlbum();
        }

        return $this->actionGetAlbums();
    }

    /**
     * Показывает кнопку для новой админки.
     *
     * @return int
     */
    protected function actionShowIframe()
    {
        return $this->render(new view\Iframe([]));
    }

    /**
     * запустить сборщик мусора.
     */
    protected function startScavenger()
    {
        // запросить
        $aRows = TmpModule::getOldRows();
        if (!$aRows) {
            return;
        }

        // набор id для удаления
        $aIdList = [];

        // перебрать все записи
        foreach ($aRows as $aRow) {
            // id в список удаления
            $aIdList[] = $aRow['id'];

            // проверить наличие записей с откатом разделов
            if (mb_strpos($aRow['value'], '..')) {
                continue;
            }

            // удаление файла
            if (is_file(WEBPATH . $aRow['value'])) {
                unlink(WEBPATH . $aRow['value']);
            }
        }

        // удалние записей по списку
        TmpModule::delById($aIdList);
    }

    /**
     * Возвращает список альбомов.
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function actionGetAlbums()
    {
        /* список альбомов текущего раздела */
        $this->iCurrentAlbumId = 0;
        $this->setPanelName(\Yii::t('gallery', 'albums'), true);

        /* Выбираем данные для списка */
        $aAlbums = gallery\Album::getBySection($this->sectionId(), false);
        $aAlbums = gallery\Album::setCountsAndPreview($aAlbums);

        foreach ($aAlbums as &$aAlbum) {
            $aAlbum['url'] = (isset($aAlbum['album_img']) && $aAlbum['album_img']) ? $aAlbum['album_img'] : $this->getModuleWebDir() . '/img/no_photo.png';
            $aAlbum['name'] = $aAlbum['title'];
            $aAlbum['header_info'] = sprintf('id=%d, %d %s', $aAlbum['id'], $aAlbum['album_count'], \Yii::t('gallery', 'photos'));
            $aAlbum['lastmod'] = $aAlbum['creation_date'];
            $aAlbum['active'] = $aAlbum['visible'] ? 'checked' : '';
            $aAlbum['size'] = 0;
        }// each picture

        /* Записываем данные на отправку */
        $this->setData('albums', $aAlbums);
        /* Добавление библиотек для работы */
        $this->addLibClass('PhotoSorter');
        $this->addLibClass('PhotoAlbumListView');

        /* Добавляем css файл для */
        $this->addCssFile('gallery.css');

        /* php событие при клике */
        $this->setData('clickAction', 'showAlbum');

        $this->setCmd('show_albums_list');

        $this->render(new Adm\Gallery\view\GetAlbums());

        return psComplete;
    }

    // func

    /**
     * Изменение видимости фотографии.
     */
    public function actionAlbumActiveChange()
    {
        gallery\Album::toggleActiveAlbum($this->get('data'));
    }

    /**
     * Описание альбома, список изображений в нем.
     *
     * @param array $aShowData
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function actionShowAlbum($aShowData = [])
    {
        /* Сообщения */
        if (count($aShowData)) {
            $time = 2000 + count($aShowData['errors']) * 2000;

            if ($aShowData['iUpload']) {
                $sMsg = \Yii::t('Files', 'loadingPro', [$aShowData['iUpload'], $aShowData['iCount']]);
                if (count($aShowData['errors'])) {
                    $sMsg .= '<br>' . implode('<br>', $aShowData['errors']);
                }

                $this->addMessage($sMsg, '', $time);
            } else {
                $sMsg = \Yii::t('files', 'noLoaded', [$aShowData['iUpload'], $aShowData['iCount']]);
                if (count($aShowData['errors'])) {
                    $sMsg .= '<br>' . implode('<br>', $aShowData['errors']);
                }
                $this->addError($sMsg);
            }
        }

        // очистить контейнер загруки
        $this->clearUploadedData();

        $aData = $this->get('data', []);

        if ($this->iCurrentAlbumId) {
            $aData['id'] = $this->iCurrentAlbumId;
        }

        if (!(int) $iAlbumId = $aData['id']) {
            throw new \Exception(\Yii::t('gallery', 'albumError'));
        }
        $aAlbum = gallery\Album::getById($iAlbumId);

        if (!$aAlbum) {
            throw new \Exception('Альбом не найден');
        }
        $this->iCurrentAlbumId = $iAlbumId;

        /* Устанавливаем название вкладки  */
        $this->setPanelName(sprintf(\Yii::t('gallery', 'album') . ' "%s" [#%s]', $aAlbum['title'], $iAlbumId), true);

        /* Выбираем данные для списка */
        $aItems = gallery\Photo::getFromAlbum($iAlbumId);

        $aImages = [];

        if ($aItems) {
            foreach ($aItems as $aImage) {
                $aImages[] = [
                    'url' => $aImage['thumbnail'],
                    'name' => $aImage['title'],
                    'size' => 0,
                    'lastmod' => $aImage['creation_date'],
                    'id' => $aImage['id'],
                    'album_id' => $aImage['album_id'],
                    'active' => $aImage['visible'] ? 'checked' : '',
                ];
            }
        }// each picture

        /* Записываем данные на отправку */
        $this->setData('images', $aImages);

        /* Добавление библиотек для работы */
        $this->addLibClass('PhotoSorter');
        $this->addLibClass('PhotoAddField');
        $this->addLibClass('PhotoListView');

        // дополнительный текст для списка
        $this->setData(
            'addText',
            sprintf(
                \Yii::t('gallery', 'loadNotice'),
                Files::getMaxUploadSize() / 1024 / 1024,
                Image::getMaxLineSize(),
                implode(', ', Image::getAllowImageTypes())
            )
        );

        $this->addJsFile('jquery.min.js');
        $this->addJsFile('cropper.min.js');
        $this->addCssFile('cropper.min.css');

        /* Добавляем css файл для */
        $this->addCssFile('gallery.css');

        /* php событие при клике */
        $this->setData('clickAction', 'showImage');

        $this->setCmd('show_photos_list');

        $this->render(new Adm\Gallery\view\ShowAlbum([
            'notOnlyAlbumEditor' => !$this->onlyAlbumEditor,
            'popup' => $this->popup,
        ]));

        return psComplete;
    }

    // func

    /**
     * Состояние: Показать форматы изображения.
     *
     * @param string $sFormatNameActive Имя активного формата
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function actionShowImage($sFormatNameActive = '')
    {
        $aData = $this->get('data');

        if (!$iImageId = $aData['id']) {
            throw new \Exception(\Yii::t('gallery', 'noImageError'));
        }
        /* Получить изображение */
        $aImage = gallery\Photo::getImage($iImageId);
        if (!$aImage) {
            throw new \Exception(\Yii::t('gallery', 'noImageError'));
        }
        $this->iCurrentAlbumId = $aImage['album_id'];

        if (!$aImage['images_data'] = json_decode($aImage['images_data'], true)) {
            throw new \Exception(\Yii::t('gallery', 'badImageError'));
        }
        /* Получить Набор форматов для изображения */
        $iProfileId = gallery\Album::getProfileId($this->iCurrentAlbumId);
        $aFormats = gallery\Format::getByProfile($iProfileId, true);

        if (!$aFormats) {
            throw new \Exception(\Yii::t('gallery', 'noFormatsError'));
        }
        /* Собираем массив данных по изображению */
        $aTabs = [];
        $i = $iActiveTabIndex = 0;
        foreach ($aFormats as $aFormat) {
            if (isset($aImage['images_data'][$aFormat['name']]) and $aFormat['active']) {
                ++$i;
                $aImageItem['src'] = $aImage['images_data'][$aFormat['name']]['file'];
                $aImageItem['name'] = $aFormat['name'];
                $aImageItem['title'] = ($aFormat['title']) ? $aFormat['title'] : "Размер ({$i})";
                $aImageItem['width'] = ($aImage['images_data'][$aFormat['name']]['width']) ? $aImage['images_data'][$aFormat['name']]['width'] : '*';
                $aImageItem['height'] = ($aImage['images_data'][$aFormat['name']]['height']) ? $aImage['images_data'][$aFormat['name']]['height'] : '*';
                $aTabs[] = $aImageItem;
                // Активировать таб заданного формата при показе
                if ($sFormatNameActive === $aImageItem['name']) {
                    $iActiveTabIndex = $i - 1;
                }
            }
        }
        // дополнительная библиотека для отображения
        $this->addLibClass('PhotoImg');

        /* Установить заголовок панели */
        if (!empty($aImage['title'])) {
            $this->setPanelName(sprintf(\Yii::t('gallery', 'module_editImage') . ' "%s"', $aImage['title']), true);
        } else {
            $this->setPanelName(\Yii::t('gallery', 'module_editImage'), true);
        }

        $sSeoClass = $this->sSeoClass;

        /** @var seo\SeoPrototype $oSeo */
        if (!class_exists($sSeoClass) || !(($oSeo = new $sSeoClass()) instanceof seo\SeoPrototype)) {
            throw new UserException("Invalid class [{$sSeoClass}]");
        }
        // Показывать заглушку seo-блока, если неизвестен раздел и родительская сущность использует индивидуальные seo-шаблоны для раздела
        $bShowStubSeo = (!$this->sectionId() and $oSeo->getIndividualTemplate4Section());

        if (!$bShowStubSeo) {
            $aData = explode(':', $this->iEntityId);
            $sCard = $aData[1] ?? '';

            $oSeo->setSectionId($this->sectionId());
            $oSeo->setExtraAlias($sCard);

            if (empty($aImage['alt_title']) && in_array('altTitle', $oSeo->editableSeoTemplateFields())) {
                $aImage['alt_title'] = $oSeo->parseField('altTitle', false);
            }

            if (empty($aImage['title']) && in_array('nameImage', $oSeo->editableSeoTemplateFields())) {
                $aImage['title'] = $oSeo->parseField('nameImage', false);
            }
        }

        $this->render(new Adm\Gallery\view\ShowImage([
            'aTabs' => $aTabs,
            'iActiveTabIndex' => $iActiveTabIndex,
            'aImage' => $aImage,
            'iImageId' => $iImageId,
            'bShowStubSeo' => $bShowStubSeo,
        ]));

        return psComplete;
    }

    // func

    /**
     * Обновляет заголовочные данные изображения.
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function actionUpdateImage()
    {
        $aData = $this->get('data');

        if (!count($aData)) {
            throw new \Exception(\Yii::t('gallery', 'noSaveImage'));
        }
        if (!(int) $iImageId = $aData['id']) {
            throw new \Exception(\Yii::t('gallery', 'noSaveImage'));
        }
        /** @var seo\SeoPrototype $oSeo */
        if (!class_exists($this->sSeoClass) || !(($oSeo = new $this->sSeoClass()) instanceof seo\SeoPrototype)) {
            throw new UserException("Invalid [{$this->sSeoClass}]class");
        }
        $bUseSeo = ($this->sectionId() || !$oSeo->getIndividualTemplate4Section());

        if ($bUseSeo) {
            $aTemp = explode(':', $this->iEntityId);
            $sCard = $aTemp[1] ?? '';

            $oSeo->setSectionId($this->sectionId());
            $oSeo->setExtraAlias($sCard);

            if (in_array('altTitle', $oSeo->editableSeoTemplateFields())) {
                if (seo\Api::prepareRawString($aData['alt_title']) == seo\Api::prepareRawString($oSeo->parseField('altTitle', false))) {
                    $aData['alt_title'] = '';
                }
            }

            if (in_array('nameImage', $oSeo->editableSeoTemplateFields())) {
                if (seo\Api::prepareRawString($aData['title']) == seo\Api::prepareRawString($oSeo->parseField('nameImage', false))) {
                    $aData['title'] = '';
                }
            }

            gallery\Photo::setImage([
                'title' => $aData['title'],
                'visible' => ($aData['visible']) ? 1 : 0,
                'description' => $aData['description'],
                'alt_title' => $aData['alt_title'],
            ], $iImageId);
        } else {
            gallery\Photo::setImage([
                'visible' => ($aData['visible']) ? 1 : 0,
                'description' => $aData['description'],
            ], $iImageId);
        }

        /* вывод списка */
        return $this->actionShowAlbum();
    }

    // func

    protected function actionDeleteImage()
    {
        $aData = $this->get('data');

        if (!count($aData)) {
            throw new \Exception(\Yii::t('gallery', 'noDeleteImage'));
        }
        if (!(int) $iImageId = $aData['id']) {
            throw new \Exception(\Yii::t('gallery', 'noDeleteImage'));
        }
        /* Обновляем данные изображения */

        $mError = '';
        $bRes = gallery\Photo::removeImage($iImageId, $mError);

        if (!$bRes) {
            throw new \Exception($mError);
        }
        $this->addModuleNoticeReport(\Yii::t('gallery', 'deleteImage'), \Yii::t('gallery', 'photoId') . " {$iImageId}");

        return $this->actionShowAlbum();
    }

    // func

    /**
     * Групповое удаление изображений.
     */
    protected function actionGroupDel()
    {
        // набор входных данных для удаления
        $aInList = $this->get('delItems');

        // проверить принадлежность целевому альбому
        $aDelList = Api::validateIdList($aInList, $this->iCurrentAlbumId);

        // удалить по списку
        $iCnt = 0;

        foreach ($aDelList as $iId) {
            if (gallery\Photo::removeImage($iId)) {
                ++$iCnt;
            }
        }

        $this->addMessage(\Yii::t('gallery', 'deleteImagesPro', [$iCnt, count($aInList)]));
        $this->addModuleNoticeReport(\Yii::t('gallery', 'deleteImage'), $aDelList);
        $this->actionShowAlbum();
    }

    /**
     * Изменение видимости фотографии.
     */
    public function actionPhotoActiveChange()
    {
        $iPhotoId = $this->get('data');
        gallery\Photo::toggleActivePhoto($iPhotoId);
    }

    /**
     * Загрузка нового изображения для определенного формата.
     */
    protected function actionLoadNewImageForFormat()
    {
        $this->setPanelName(\Yii::t('gallery', 'module_loadImage'), true);

        // Обработка входных данных
        $sFormat = $this->get('formatName');
        $iImageId = $this->get('imageId');

        if (!$sFormat or !$iImageId) {
            throw new \Exception(\Yii::t('gallery', 'loadDataError'));
        }
        if (!$iAlbumId = (int) $this->iCurrentAlbumId) {
            throw new \Exception(\Yii::t('gallery', 'noAlbumError'));
        }
        // Загрузка изображений, перемещение в целевую директорию
        $sSourceFN = Api::uploadFile($iAlbumId);

        if (!$sSourceFN) {
            $aErrors = Api::getErrorUploadList();
            $sErrorText = isset($aErrors[0]) ? ' (' . $aErrors[0] . ')' : '';
            throw new UserException(\Yii::t('gallery', 'noLoadImage') . $sErrorText);
        }

        $this->actionReCropForm($iImageId, $sFormat, $sSourceFN);
    }

    /**
     * Изменений кропинга для определенного формата изображения.
     *
     * @param int $iImageId
     * @param string $sFormatName
     * @param string $sSourceFN
     *
     * @throws UserException
     * @throws \Exception
     */
    protected function actionReCropForm($iImageId = 0, $sFormatName = '', $sSourceFN = '')
    {
        // Обработка входных данных
        $aData = $this->get('data');

        $sFormat = $aData['selectedFormat'] ?? $sFormatName;
        $iImageId = $aData['id'] ?? $iImageId;
        if (!$sFormat or !$iImageId) {
            throw new \Exception(\Yii::t('gallery', 'loadDataError'));
        }
        $this->setPanelName(\Yii::t('gallery', 'editImageTab'), true);

        // получение информации о текущем формате
        $aFormat = gallery\Format::getByName($sFormat, gallery\Album::getProfileId($this->iCurrentAlbumId));

        if (!$aFormat) {
            throw new \Exception(\Yii::t('gallery', 'badFormat'));
        }
        // получение исходного изображения
        if (!$sSourceFN) {
            $sMaxFormatPath = gallery\Photo::getMaxFormatByPhotoId($iImageId);

            if ($sMaxFormatPath === false) {
                throw new UserException(\Yii::t('gallery', 'error_selecting_format_for_recrop'));
            }

            $sSourceFN = $sMaxFormatPath;
        }

        // создать миниатюру
        $aCropMin = Api::createCropMin($sSourceFN, $this->iCurrentAlbumId);

        // добавить в сессию запись о загруженном файле и о миниатюре и получить ключ
        $sCropFN = $aCropMin['file'];
        $this->aUploadedData = [
                'crop' => $sCropFN,
                'source' => $sSourceFN,
                'crop_id' => TmpModule::create('crop', $sCropFN),
                'source_id' => TmpModule::create('source', $sSourceFN),
            ];

        $aCropMin['file'] = str_replace('//', '/', $sSourceFN);

        list($w_i, $h_i) = getimagesize(WEBPATH . $aCropMin['file']);
        $aCropMin['naturalWidth'] = $w_i;
        $aCropMin['naturalHeight'] = $h_i;

        /*Обработка галки "РЕСАЙЗ по большей стороне"*/
        if (Image::needRotation($aFormat[0]['width'], $aFormat[0]['height'], $w_i, $h_i, $aFormat[0]['resize_on_larger_side'])) {
            $iTmp = $aFormat[0]['width'];
            $aFormat[0]['width'] = $aFormat[0]['height'];
            $aFormat[0]['height'] = $iTmp;
        }

        if ((($aFormat[0]['height'] == '0') and ($aFormat[0]['width'] > $w_i))
                or (($aFormat[0]['width'] == '0') and ($aFormat[0]['height'] > $h_i))) {
            $aFormat[0]['scale_and_crop'] = '1';
        }

        /*Если по ширине исходник меньше чем формат. включим вписывание*/
        if ($aFormat[0]['width'] >= $w_i) {
            $aFormat[0]['scale_and_crop'] = 1;
        }

        /*Если по высоте исходник меньше чем формат. включим вписывание*/
        if ($aFormat[0]['height'] >= $h_i) {
            $aFormat[0]['scale_and_crop'] = 1;
        }

        /*Если хоть 1 из параметров динамический. включим вписывание*/
        if ($aFormat[0]['width'] == 0 || $aFormat[0]['height'] == 0) {
            $aFormat[0]['scale_and_crop'] = 1;

            $aOperated = Image::getOperatedSizes($aFormat[0]['width'], $aFormat[0]['height'], $w_i, $h_i);
            $aFormat[0]['width'] = $aOperated['width'];
            $aFormat[0]['height'] = $aOperated['height'];
        }

        $aCropMin['calculations'] = Image::operateCalculation($aFormat[0]['width'], $aFormat[0]['height'], $w_i, $h_i, $aFormat[0]['scale_and_crop']);

        // данные о миниатюре для отображения кроп интерфейса
        $this->setData('cropData', $aCropMin);
        $this->setData('formatsData', $aFormat);

        // Собираем массив данных по изображению
        $aTabs['name'] = 'formats';
        $aTabs['title'] = \Yii::t('gallery', 'module_images');
        $aTabs['value'] = ['cropData' => $aCropMin, 'formatsData' => $aFormat];
        $aTabs['cropData'] = $aCropMin;
        $aTabs['formatsData'] = $aFormat;
        $aTabs['subtext'] = \Yii::t('gallery', 'module_load_subtext');

        $this->addLibClass('PhotoResizer');

        $this->render(new Adm\Gallery\view\ReCropForm([
            'aTabs' => $aTabs,
            'iImageId' => $iImageId,
        ]));
    }

    protected function actionBackToDefault()
    {
        $iAlbumId = (int) $this->iCurrentAlbumId;
        if (!$iAlbumId) {
            throw new \Exception(\Yii::t('gallery', 'noAlbumError'));
        }
        $aData = $this->get('data');

        // id профиля для альбома
        $iProfileId = gallery\Album::getProfileId($iAlbumId);
        if (!$iProfileId) {
            throw new \Exception(\Yii::t('gallery', 'badData'));
        }
        $aFormats = gallery\Format::getByProfile($iProfileId, true);

        $aCrop = [];
        if (count($aFormats)) {
            foreach ($aFormats as $iKey => $aFormat) {
                //if(strpos($sKey,'cropData_') !== false)
                $aCrop[$aFormat['name']] = ['x' => 0, 'y' => 0, 'width' => 0, 'height' => 0];
            }
        }

        if (!count($aCrop)) {
            throw new \Exception(\Yii::t('gallery', 'noDataToSave'));
        }
        $mProfileId = [
            'crop' => $aCrop,
            'iProfileId' => $iProfileId,
        ];

        // запросить файл
        $sImagePath = $aData['source'];

        $sImageFullPath = WEBPATH . $sImagePath;

        $aFormat = gallery\Format::getByName($aData['format']['name'], $aData['format']['profile_id']);

        $aNewImage = gallery\Photo::processImage($sImageFullPath, $mProfileId, $iAlbumId, false, true, $sError, Api::cropHeight, false, $aData['format']['resize_on_larger_side'], $aFormat);

        /*Скропили картинку как будто автоматически*/
        /*Сохранить данные о ней*/
        $aAlbum = gallery\Photo::getImage($aData['id']);
        $aImagesData = json_decode($aAlbum->getAttribute('images_data'), true);

        $aImagesData[$aData['format']['name']] = $aNewImage[$aData['format']['name']];

        $aAlbum->setAttribute('images_data', json_encode($aImagesData));

        /*Если рекропнули thumbnail_overlay, значит надо изменить и фотку thumbnail*/
        if (isset($aNewImage['thumbnail_overlay']['file'])) {
            $aAlbum->setAttribute('thumbnail', $aNewImage['thumbnail_overlay']['file']);
        }

        $aAlbum->save();

        $this->actionShowImage(key($aCrop));
    }

    /**
     * Сохранение перекроппинного изображения для определенного формата.
     *
     * @throws \Exception
     */
    protected function actionSaveReCropImage()
    {
        // id отображаемого альбома
        $iAlbumId = (int) $this->iCurrentAlbumId;
        if (!$iAlbumId) {
            throw new \Exception(\Yii::t('gallery', 'noAlbumError'));
        }
        // id профиля для альбома
        $iProfileId = gallery\Album::getProfileId($iAlbumId);
        if (!$iProfileId) {
            throw new \Exception(\Yii::t('gallery', 'badData'));
        }
        $aData = $this->get('data');

        $aCrop = $aData['cropdata'];
        $aFormat = $aData['format'];
        // запросить файл

        Api::operateAfterRecrop($aData, $aFormat, $iAlbumId);

        $this->actionShowImage(key($aCrop));
    }

    /**
     * Форма  Добавления / редактирования описания альбома.
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function actionAddUpdAlbum()
    {
        $this->setPanelName(\Yii::t('gallery', 'module_add'), true);
        $iAlbumId = false;

        try {
            if ($this->iCurrentAlbumId) {
                $iAlbumId = $this->iCurrentAlbumId;
            }

            /* Получаем данные формата или заготовку под новый формат */
            $aValues = $iAlbumId ? gallery\Album::getById($iAlbumId) : gallery\Album::getAlbumBlankValues();

            /* Если альбом новый - ставим профиль по умолчанию */
            if (!$aValues['id']) {
                $aValues['profile_id'] = Profile::getDefaultId(Profile::TYPE_SECTION);
            } else {
                // Если альбом не новый, то не давать возможность изменить профиль галереи
                $aValues['profile_id'] = ($aProfile = Profile::getById($aValues['profile_id'])) ? $aProfile['title'] : \Yii::t('gallery', 'profiles_notfound');
            }

            // добавление SEO блока полей
            $aDataGallery = (is_array($aValues)) ? $aValues : $aValues->getAttributes();

            $this->render(new Adm\Gallery\view\AddUpdAlbum([
                'aActiveProfiles' => Profile::getActiveByType(Profile::TYPE_SECTION, true),
                'iAlbumId' => $iAlbumId,
                'aValues' => $aValues,
                'iSectionId' => $this->sectionId(),
                'oSeo' => new \skewer\build\Adm\Gallery\Seo(0, $this->sectionId(), $aDataGallery),
            ]));
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        return psComplete;
    }

    // func

    /**
     * Сохраняет Описание альбома.
     *
     * @throws \Exception
     */
    protected function actionSaveAlbum()
    {
        Api::createTempDir();
        $aData = $this->get('data');

        if (!count($aData)) {
            throw new \Exception(\Yii::t('gallery', 'noUploadData'));
        }
        $iAlbumId = ($aData['id']) ? $aData['id'] : false;

        /* Установка дополнительных значений */
        $aData['owner'] = 'section';              // владельца
        $aData['section_id'] = $this->sectionId(); // родительского раздела

        if ($iAlbumId) { // Изменение альбома с валидацией полей
            if (!$oAlbum = gallery\Album::getById($iAlbumId)) {
                throw new \Exception(\Yii::t('gallery', 'general_field_empty'));
            }
        } else {// Вставка нового альбома
            $oAlbum = new gallery\models\Albums();
            if (isset($aData['section_id']) and $aData['section_id']) {
                $aData['priority'] = gallery\models\Albums::find()
                    ->where(['section_id' => $aData['section_id']])
                    ->max('priority') + 1;
            }
        }

        $aOldAttributes = $oAlbum->getAttributes();
        $oAlbum->setAttributes($aData);

        if (!$oAlbum->save()) {
            throw new ui\ARSaveException($oAlbum);
        }
        if (seo\Service::$bAliasChanged) {
            $this->addMessage(\Yii::t('tree', 'urlCollisionFlag', ['alias' => $oAlbum->alias]));
        }

        $aData['id'] = $oAlbum->id;

        // сохранение SEO данных
        seo\Api::saveJSData(
            new \skewer\build\Adm\Gallery\Seo(ArrayHelper::getValue($aOldAttributes, 'id', 0), $this->sectionId(), $aOldAttributes),
            new \skewer\build\Adm\Gallery\Seo($oAlbum->id, $this->sectionId(), $oAlbum->getAttributes()),
            $aData,
            $this->sectionId()
        );

        /* Если все нормально, устанавливаем в кач. текущего альбома, тот, который был определен */
        $this->iCurrentAlbumId = $oAlbum->id;

        $this->addModuleNoticeReport(\Yii::t('gallery', 'saveAlbumReport'), \Yii::t('gallery', 'album_id') . " = {$oAlbum->id}");

        seo\Api::setUpdateSitemapFlag();

        /* вывод изображений альбома */
        $this->actionShowAlbum();
    }

    // func

    /**
     * Автоматический кроп фотографий при мультифайловой загрузки.
     *
     * @param array $aFiles
     *
     * @throws \Exception
     */
    protected function actionMultiUploadImages($aFiles = [])
    {
        // id отображаемого альбома
        $iAlbumId = (int) $this->iCurrentAlbumId;
        if (!$iAlbumId) {
            throw new \Exception(\Yii::t('gallery', 'noAlbumError'));
        }
        // id профиля для альбома
        $iProfileId = gallery\Album::getProfileId($iAlbumId);
        if (!$iProfileId) {
            throw new \Exception(\Yii::t('gallery', 'badData'));
        }
        // набор форматов альбома
        $aFormats = gallery\Format::getByProfile($iProfileId, true);
        if (!$aFormats) {
            throw new \Exception(\Yii::t('gallery', 'noFormatsError'));
        }
        unset($aFormats['thumbnail']);

        $aErrors = Api::getErrorUploadList();
        $iCount = count($aFiles) + count($aErrors);

        foreach ($aFiles as $k => $sSourceFN) {
            try {
                $aCrop = [];
                if (count($aFormats)) {
                    foreach ($aFormats as $iKey => $aFormat) {
                        //if(strpos($sKey,'cropData_') !== false)
                        $aCrop[$aFormat['name']] = ['x' => 0, 'y' => 0, 'width' => 0, 'height' => 0];
                    }
                }

                $sTitle = '';
                $sAltTitle = '';
                $sDescription = '';

                if (!count($aCrop)) {
                    throw new \Exception(\Yii::t('gallery', 'noDataToSave'));
                }
                // запросить файл
                $sImagePath = $sSourceFN;

                $sImageFullPath = WEBPATH . $sImagePath;

                /* Обработка изображения согласно профилю настроек, перемещение созданных файлов в целевые директории */
                if (!$sImagePath) {
                    throw new \Exception(\Yii::t('gallery', 'noLoadImage'));
                }
                $sError = false;

                $mProfileId = [
                    'crop' => $aCrop,
                    'iProfileId' => $iProfileId,
                ];

                $aNewImage = gallery\Photo::processImage($sImageFullPath, $mProfileId, $iAlbumId, false, false, $sError, Api::cropHeight);

                if (!$aNewImage or $sError) {
                    throw new \Exception($sError);
                }
                $sThumbnail = (isset($aNewImage['thumbnail'])) ? $aNewImage['thumbnail'] : '';

                unset($aNewImage['thumbnail']);

                /* Сохранение сущности в БД */
                $iImageId = gallery\Photo::setImage([
                    'title' => $sTitle,
                    'alt_title' => $sAltTitle,
                    'visible' => 1,
                    'album_id' => $iAlbumId,
                    'thumbnail' => $sThumbnail,
                    'description' => $sDescription,
                    'images_data' => json_encode($aNewImage),
                    'source' => $sImagePath,
                ]);

                // очистить контейнер загруки
                $this->clearUploadedData();

                if (!$iImageId) {
                    throw new \Exception(\Yii::t('gallery', 'noSaveError'));
                }
            } catch (\Exception $e) {
                $aErrors[] = $k . ': ' . $e->getMessage();
            }
        }

        $aData = [
            'errors' => $aErrors,
            'iCount' => $iCount,
            'iUpload' => $iCount - count($aErrors),
        ];

        $this->actionShowAlbum($aData);
    }

    /**
     * Загружает изображение на сервер и обрабатывает согласно профилю.
     *
     * @throws \Exception
     */
    protected function actionUploadImage()
    {
        if (!$iAlbumId = (int) $this->iCurrentAlbumId) {
            throw new \Exception(\Yii::t('gallery', 'noAlbumError'));
        }
        $iProfileId = gallery\Album::getProfileId($iAlbumId);
        if (!$iProfileId) {
            throw new \Exception(\Yii::t('gallery', 'badData'));
        }
        // набор форматов альбома
        $aFormats = gallery\Format::getByProfile($iProfileId, true);
        if (!$aFormats) {
            throw new \Exception(\Yii::t('gallery', 'noFormatsError'));
        }
        unset($aFormats['thumbnail']);

        $this->setPanelName(\Yii::t('gallery', 'module_load'), true);

        // Загрузка изображений, перемещение в целевую директорию
        $sSourceFN = Api::uploadFile($iAlbumId);

        if (is_string($sSourceFN)) {
            $sSourceFN = [$sSourceFN];
        }

        $this->actionMultiUploadImages($sSourceFN);

        $oFaviconParam = \skewer\components\design\model\Params::find()
            ->where(['name' => 'page.favicon'])
            ->one();

        /*Если загрузили фото в альбом фавикона. надо перестроить*/
        if ($iAlbumId == $oFaviconParam->value) {
            UtilsApi::rebuildFavicon();
        }
    }

    // func

    /**
     * Выполнение ресайза уже загруженного фото.
     *
     * @throws \Exception
     */
    protected function actionResizePhoto()
    {
        // id отображаемого альбома
        $iAlbumId = (int) $this->iCurrentAlbumId;
        if (!$iAlbumId) {
            throw new \Exception(\Yii::t('gallery', 'noAlbumError'));
        }
        // id профиля для альбома
        $iProfileId = gallery\Album::getProfileId($iAlbumId);
        if (!$iProfileId) {
            throw new \Exception(\Yii::t('gallery', 'badData'));
        }
        // получить ключ кропа
        $aCrop = [];
        $aData = $this->get('data');
        if (is_array($aData)) {
            foreach ($aData as $sKey => $aVal) {
                if (mb_strpos($sKey, 'cropData_') !== false) {
                    $aCrop[mb_substr($sKey, 9)] = $aVal;
                }
            }
        }

        $sTitle = $aData['title'] ?? '';
        $sAltTitle = $aData['alt_title'] ?? '';
        $sDescription = $aData['description'] ?? '';
        $aData = $this->aUploadedData;

        if (!$aData or !count($aCrop)) {
            throw new \Exception(\Yii::t('gallery', 'noSataToSave'));
        }
        $iSourceId = $aData['source_id'];

        // запросить файл
        $sImagePath = $aData['source'];
        $sImageFullPath = WEBPATH . $sImagePath;

        /* Обработка изображения согласно профилю настроек, перемещение созданных файлов в целевые директории */
        if (!$sImagePath) {
            throw new \Exception(\Yii::t('gallery', 'noLoadImage'));
        }
        $sError = false;

        $mProfileId = [
            'crop' => $aCrop,
            'iProfileId' => $iProfileId,
        ];
        $aNewImage = gallery\Photo::processImage($sImageFullPath, $mProfileId, $iAlbumId, false, true, $sError, Api::cropHeight, $iSourceId);

        if (!$aNewImage or $sError) {
            throw new \Exception($sError);
        }
        $sThumbnail = (isset($aNewImage['thumbnail'])) ? $aNewImage['thumbnail'] : '';

        unset($aNewImage['thumbnail']);

        /* Сохранение сущности в БД */
        $iImageId = gallery\Photo::setImage([
            'title' => $sTitle,
            'alt_title' => $sAltTitle,
            'source' => $sImagePath,
            'visible' => 1,
            'album_id' => $iAlbumId,
            'thumbnail' => $sThumbnail,
            'description' => $sDescription,
            'images_data' => json_encode($aNewImage),
        ]);

        // очистить контейнер загруки
        $this->clearUploadedData();

        if (!$iImageId) {
            throw new \Exception(\Yii::t('gallery', 'noSataToSave'));
        }
        $this->set('data', ['id' => $iImageId]);
        $this->addModuleNoticeReport(\Yii::t('gallery', 'loadImageNotice'), \Yii::t('gallery', 'photoId') . " = {$iImageId}");

        $this->actionShowImage();
    }

    /**
     * Удаляет файлы и очищает контейнер загрузки.
     */
    protected function clearUploadedData()
    {
        // выйти, если уже очищен
        if (!$this->aUploadedData) {
            return;
        }

        // удалить миниатюру и исходник
        unlink(WEBPATH . $this->aUploadedData['crop']);

        TmpModule::delById($this->aUploadedData['crop_id']);
        TmpModule::delById($this->aUploadedData['source_id']);

        // очистить контейнер данных загрузки
        $this->aUploadedData = [];
    }

    /**
     * Удаляет выбранный альбом и фотографии к нему.
     *
     * @throws \Exception
     */
    protected function actionDelAlbum()
    {
        /* Данные по альбому */
        $aData = $this->get('data');

        if (!isset($aData['id']) or (!$iAlbumId = (int) $aData['id'])) {
            throw new \Exception(\Yii::t('gallery', 'albumError'));
        }
        /* Удаление альбома */
        $mError = false;
        if (!gallery\Album::removeAlbum($iAlbumId, $mError)) {
            throw new \Exception($mError);
        }
        /* Сброс значения текущго альбома */
        $this->iCurrentAlbumId = false;

        /*Вывод списка альбомов для текущего раздела*/
        $this->actionGetAlbums();

        seo\Api::setUpdateSitemapFlag();

        $this->addModuleNoticeReport(\Yii::t('gallery', 'deleteAlbumReport'), \Yii::t('gallery', 'deleteAlbumName') . $aData['title']);
    }

    /**
     * Сортировка картинок.
     */
    protected function actionSortImages()
    {
        $iItemId = $this->get('itemId');
        $iTargetItemId = $this->get('targetId');
        $sOrderType = $this->get('orderType');

        if (!$iItemId or !$iTargetItemId or !$sOrderType) {
            throw new \Exception(\Yii::t('gallery', 'noSort'));
        }
        $iItemId = (int) str_replace('horizontal_sort', '', $iItemId);
        $iTargetItemId = (int) str_replace('horizontal_sort', '', $iTargetItemId);

        gallery\Photo::sortImages($iItemId, $iTargetItemId, $sOrderType);
    }

    /**
     * Сортировка альбомов.
     */
    protected function actionSortAlbums()
    {
        $iItemId = $this->get('itemId');
        $iTargetItemId = $this->get('targetId');
        $sOrderType = $this->get('orderType');

        if (!$iItemId or !$iTargetItemId or !$sOrderType) {
            throw new \Exception(\Yii::t('gallery', 'noSort'));
        }
        $iItemId = (int) str_replace('horizontal_sort', '', $iItemId);
        $iTargetItemId = (int) str_replace('horizontal_sort', '', $iTargetItemId);

        gallery\Album::sortAlbums($iItemId, $iTargetItemId, $sOrderType);
    }

    /**
     * Групповое удаление альбомов.
     */
    protected function actionGroupAlbumDel()
    {
        // набор входных данных для удаления
        $aInList = $this->get('delItems');

        // проверить принадлежность целевому альбому
        $aDelList = Api::validateIdAlbumsList($aInList, $this->sectionId());

        // удалить по списку
        $iCnt = 0;
        foreach ($aDelList as $iId) {
            if (gallery\Album::removeAlbum($iId)) {
                ++$iCnt;
            }
        }

        $this->addMessage(\Yii::t('gallery', 'deleteAlbumsPro', [$iCnt, count($aInList)]));
        $this->addModuleNoticeReport(\Yii::t('gallery', 'albumDeleting'), $aDelList);
        $this->actionGetAlbums();
    }

    /**
     * Интерфейс с кнопкой создания альбома для раздела.
     *
     * @return int
     */
    protected function actionNonAlbum()
    {
        /* список альбомов текущего раздела */
        $this->setPanelName(\Yii::t('gallery', 'albums'), true);

        /* Записываем данные на отправку */
        $this->setData('albums', []);
        /* Добавление библиотек для работы */

        $this->render(new Adm\Gallery\view\NonAlbum([]));

        return psComplete;
    }

    /** Действие: создание альбома для раздела */
    protected function actionCreateAlbum4Section()
    {
        $iNewId = gallery\Album::setAlbum([
            'owner' => 'section',  // владелец
            'section_id' => $this->sectionId(), // родительский раздел
            'profile_id' => Profile::getDefaultId(Profile::TYPE_SECTION), // Профиль форматов
        ]);

        /* Если все нормально, устанавливаем в кач. текущего альбома, тот, который был определен */
        if ($iNewId) {
            $this->iCurrentAlbumId = $iNewId;
        }

        Parameters::setParams($this->sectionId(), 'object', 'iCurrentAlbumId', $iNewId);

        /* вывод изображений альбома */
        $this->actionShowAlbum();
    }

    /**
     * Выдача ошибки.
     *
     * @return int
     */
    private function showError()
    {
        $this->title = \Yii::t('adm', 'error');

        $this->render(new Adm\Gallery\view\ShowError([
            'sErrorText' => $this->sErrorText,
        ]));

        return psComplete;
    }

    /**
     * Настройки модуля "Галерея" в разделе.
     */
    protected function actionSettings()
    {
        $this->render(new view\SettingsInSection([
            'template_detail' => $this->getParamValue('template_detail'),
            'sWarning' => \skewer\build\Page\Gallery\Module::ALBUM_DETAIL_TPL_INLINE == $this->getParamValue('template_detail') ? $this->buildWarning() : '',
        ]));

        return psComplete;
    }

    /**
     * Сохранение настроек модуля "Галерея" в альбоме.
     */
    protected function actionSaveSettings()
    {
        $data = $this->get('data');

        $aJustifiedGalleryOptions = $aParamsInSection = [];

        if (isset($data['justifiedGalleryOption_rowHeight'], $data['``'])) {
            if ($data['justifiedGalleryOption_rowHeight'] < 100) {
                throw new UserException(\Yii::t('gallery', 'param_must_greater', ['parameter' => \Yii::t('gallery', 'justifiedGalleryOption_rowHeight')]));
            }
            if ($data['justifiedGalleryOption_maxRowHeight'] < 100) {
                throw new UserException(\Yii::t('gallery', 'param_must_greater', ['parameter' => \Yii::t('gallery', 'justifiedGalleryOption_maxRowHeight')]));
            }
            if ($data['justifiedGalleryOption_rowHeight'] > $data['justifiedGalleryOption_maxRowHeight']) {
                throw new UserException(
                    \Yii::t('gallery', 'height_above_maximum', [
                    'maxHeight' => \Yii::t('gallery', 'justifiedGalleryOption_maxRowHeight'),
                    'height' => \Yii::t('gallery', 'justifiedGalleryOption_rowHeight'), ])
                );
            }
        }

        foreach ($data as $field => $value) {
            if (mb_strpos($field, 'justifiedGalleryOption_') === 0) {
                $sOptionName = mb_substr($field, mb_strlen('justifiedGalleryOption_'));
                $aJustifiedGalleryOptions[$sOptionName] = $value;
            } else {
                $aParamsInSection[$field] = $value;
            }
        }

        if (isset($data['template_detail']) && ($data['template_detail'] == \skewer\build\Page\Gallery\Module::ALBUM_DETAIL_TPL_INLINE)) {
            $aParamsInSection['justifiedGalleryConfig'] = json_encode($aJustifiedGalleryOptions);
        }

        $this->saveParametersInSection($aParamsInSection);

        $this->actionGetAlbums();
    }

    /** Ajax-обновление формы "Настройки галереи в разделе" */
    protected function actionUpdateSettingsInSection()
    {
        $aTransfer = [];
        $aData = $this->get('formData');
        if (isset($aData['template_detail'])) {
            $aTransfer['template_detail'] = $aData['template_detail'];
            $aTransfer['sWarning'] = \skewer\build\Page\Gallery\Module::ALBUM_DETAIL_TPL_INLINE == $aData['template_detail'] ? $this->buildWarning() : '';
        }

        $this->render(new view\SettingsInSection($aTransfer));
    }

    /**
     * Сохранить параметры в текущий раздел(зона=content).
     *
     * @param array $aParams - массив параметров
     *
     * @return bool
     */
    public function saveParametersInSection($aParams)
    {
        foreach ($aParams as $sParamName => $mValue) {
            Section::setParam($this->sectionId(), $sParamName, $mValue);
        }

        return true;
    }

    /**
     * Получить значение параметра из раздела.
     *
     * @param $sFieldName - имя параметра
     *
     * @return string
     */
    public function getParamValue($sFieldName)
    {
        $sVal = Parameters::getValByName($this->sectionId(), 'content', $sFieldName, true);

        return $sVal ? $sVal : '';
    }

    /**
     * Построит сообщение об необходимости активации форматов для шаблона "Строка".
     *
     * @return string | bool
     */
    public function buildWarning()
    {
        $aAlbums = gallery\models\Albums::find()
            ->where(['section_id' => $this->sectionId()])
            ->asArray()
            ->all();

        $aProfiles = ArrayHelper::getColumn($aAlbums, 'profile_id');

        if (!$aProfiles) {
            $aProfiles[] = Profile::getByAlias(Profile::TYPE_SECTION);
        }

        $aFormats = gallery\models\Formats::find()
            ->alias('formats')->select(['formats.*', 'profiles.title as profile_title'])
            ->innerJoin(gallery\models\Profiles::tableName() . ' profiles', 'formats.profile_id = profiles.id')
            ->where([
                'AND',
                ['profile_id' => $aProfiles],
                ['formats.name' => ['preview_hor', 'preview_ver']],
                ['formats.active' => 0],
            ])
            ->asArray()
            ->all();

        if (!$aFormats) {
            return false;
        }

        $aProfiles = [];
        foreach ($aFormats as $aFormat) {
            $aProfiles[$aFormat['profile_id']]['title'] = $aFormat['profile_title'];
            $aProfiles[$aFormat['profile_id']]['formats'][] = $aFormat;
        }

        $sHtml = \Yii::$app->view->renderPhpFile(__DIR__ . \DIRECTORY_SEPARATOR . 'templates' . \DIRECTORY_SEPARATOR . 'warning.php', ['aProfiles' => $aProfiles]);

        return $sHtml;
    }
}
