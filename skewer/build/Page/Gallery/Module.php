<?php

namespace skewer\build\Page\Gallery;

use skewer\base\section\Parameters;
use skewer\base\site;
use skewer\base\site_module;
use skewer\base\SysVar;
use skewer\build\Adm\Gallery\Api;
use skewer\build\Design\Zones;
use skewer\components\gallery;
use skewer\components\seo;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Модуль фотогаллереи.
 */
class Module extends site_module\page\ModulePrototype implements site_module\Ajax
{
    /** @const string Шаблон детальной страницы альбома(Таблица) */
    const ALBUM_DETAIL_TPL_TABLE = 'showAlbum.php';

    /** @const string Шаблон детальной страницы альбома(Фоторама) */
    const ALBUM_DETAIL_TPL_FOTORAMA = 'showAlbumFotorama.php';

    /** @const string Шаблон детальной страницы альбома(Строка) */
    const ALBUM_DETAIL_TPL_INLINE = 'showAlbumInline.php';

    /** @var string Шаблон списка */
    public $template = 'albumsList.twig';

    /** @var string Шаблон детальной */
    public $template_detail = self::ALBUM_DETAIL_TPL_TABLE;

    public $openAlbum = false;

    /** @var int количество выводимых галерей */
    public $galleryOnPage = 10;

    /** @var int ограничение вывода фото в альбоме. постраничного нет */
    public $photosLimit = 150;

    /*
     * @var int id раздела источника данных
     * Если заполнено - в качестве раздела будет изпользован заданный
     *   при этом ссылки будут вести на целевую страницу
     * Если альбом один - будет сразу развернут
     * Если альбомов несколько - откроется выбранный но в разделе указанном в параметре
     */
    public $sourceSection = 0;

    private $iCurrentSection;

    /** @var bool Флаг, указывающий на то, что модуль не учавствует в разборе урл */
    public $showList;

    /**
     * {@inheritdoc}
     */
    public function onCreate()
    {
        if ($this->showList) {
            $this->setUseRouting(false);
        }
    }

    public function init()
    {
        $this->setParser(parserTwig);
        $this->iCurrentSection = $this->sectionId();

        if ($this->sourceSection) {
            $this->iCurrentSection = $this->sourceSection;
        }
    }

    // func

    /**
     * Вывод альбома по алиасу.
     *
     * @param $alias
     *
     * @throws NotFoundHttpException
     *
     * @return int
     */
    public function actionShowByAlias($alias)
    {
        $this->setStatePage(Zones\Api::DETAIL_LAYOUT);

        // Если альбомы открыты, но запрошен alias альбома то выдать 404
        if ($this->openAlbum) {
            throw new NotFoundHttpException();
        }
        if (!$aAlbum = gallery\Album::getByAlias($alias, $this->iCurrentSection)) {
            throw new NotFoundHttpException();
        }
        if (!$aAlbum['visible']) {
            throw new NotFoundHttpException();
        }
        \Yii::$app->router->setLastModifiedDate($aAlbum['last_modified_date']);

        // меняем заголовок страницы
        site\Page::setTitle($aAlbum['title']);

        // добавляем элемент в pathline
        site\Page::setAddPathItem($aAlbum['title'], gallery\Album::getUrl($aAlbum['section_id'], $aAlbum['alias'], $aAlbum['id']));

        $this->setSeo(new \skewer\build\Adm\Gallery\Seo($aAlbum['id'], $this->iCurrentSection, $aAlbum));

        $this->setData('description', $aAlbum['description']);

        $this->setPhotos($aAlbum['id']);

        return psComplete;
    }

    // func

    /**
     * Вывод по разделу.
     *
     * @throws NotFoundHttpException
     *
     * @return bool
     */
    public function actionIndex()
    {
        if (!$this->iCurrentSection) {
            throw new NotFoundHttpException();
        }
        $iPage = $this->getInt('page', 1);
        $iCount = 0;
        $aAlbums = $this->getListAlbumsWithPreview($this->galleryOnPage, $iPage, $iCount);

        \Yii::$app->router->setLastModifiedDate(gallery\models\Albums::getMaxLastModifyDate());

        if ($iCount == 1 and $iPage == 1) {
            $this->openAlbum = true;
        }

        if ($this->openAlbum) {
            $this->setData('hideBack', true);
            $aAlbums = $this->getListAlbumsWithPreview();
            $this->setPhotos(ArrayHelper::getColumn($aAlbums, 'id'));

            return psComplete;
        }

        $this->getPageLine(
            $iPage,
            $iCount,
            $this->iCurrentSection,
            [],
            ['onPage' => $this->galleryOnPage],
            'aPages',
            !$this->isMainModule()
        );

        $this->setData('albums', $aAlbums);
        $this->setData('sectionId', $this->iCurrentSection);
        $this->setTemplate($this->template);

        return psComplete;
    }

    // func

    /**
     * Получить список альбомов текущего раздела раздела c превью изображением
     *
     * @param int $iOnPage - количество на страницу
     * @param int $iPage - номер страницы
     * @param int $iCount - сюда будет возвращено общее количество
     * @param bool $bWithoutHidden - Без скрытых альбомов?
     *
     * @return array - Возвращает массив найденных альбомов
     */
    public function getListAlbumsWithPreview($iOnPage = 0, $iPage = 1, &$iCount = 0, $bWithoutHidden = true)
    {
        $aAlbums = gallery\Album::getOnlyWithImages($this->iCurrentSection, $bWithoutHidden, $iOnPage, $iPage, $iCount);

        $aAlbums = gallery\Album::setCountsAndPreview($aAlbums, true);

        foreach ($aAlbums as &$aAlbum) {
            $oSeo = new \skewer\build\Adm\Gallery\Seo($aAlbum['id'], $this->iCurrentSection, $aAlbum);

            /* Если для превью alt не прописан, то парсим его по шаблону */
            if (empty($aAlbum['alt_title'])) {
                $aAlbum['alt_title'] = $oSeo->parseField('altTitle', ['sectionId' => $this->iCurrentSection, 'label_number_photo' => $aAlbum['priorityPreview']]);
            }
        }

        return $aAlbums;
    }

    /**
     * Добавляет изображения альбомов.
     *
     * @param array $aAlbumsId Альбомы
     */
    private function setPhotos($aAlbumsId)
    {
        $iTotalCount = 0;
        $aPhotos = gallery\Photo::getListWithSeoData($aAlbumsId, $this->iCurrentSection, true, $this->photosLimit, 1, $iTotalCount);
        $this->setData('bAllImagesLoaded', ($iTotalCount == count($aPhotos)));
        $this->setData('images', $aPhotos);
        $this->setData('openAlbum', $this->openAlbum);
        $this->setData('protect', SysVar::get('Page.not_save_image_fancybox', 0));
        $this->setData('transitionEffect', SysVar::get('Page.image_change_effect', 'disable'));
        $this->setData('aAlbums', $aAlbumsId);
        $this->setData('justifiedGalleryConfig', json_encode(Api::getConfigJustifiedGallery($this->sectionId())));
        $this->setData('AlbumAlias', $this->getStr('alias', ''));
        $this->setParser(parserPHP);
        $this->setTemplate($this->template_detail);
        $this->setEnvParam('gallery_photos', 1);
    }

    public function setSeo(seo\SeoPrototype $oSeo)
    {
        $this->setEnvParam(seo\Api::SEO_COMPONENT, $oSeo);
        $this->setEnvParam(seo\Api::OPENGRAPH, '');
        site\Page::reloadSEO();
    }

    /**
     * Вернет варианты шаблонов модуля на детальной странице альбома.
     *
     * @return array
     */
    public static function getDetailTemplates()
    {
        return [
            self::ALBUM_DETAIL_TPL_TABLE => \Yii::t('gallery', 'tpl_table'),
            self::ALBUM_DETAIL_TPL_FOTORAMA => \Yii::t('gallery', 'tpl_fotorama'),
            self::ALBUM_DETAIL_TPL_INLINE => \Yii::t('gallery', 'tpl_string'),
        ];
    }

    /** Получение порции изображений для библиотеки JustifiedGallery */
    public function actionGetChunkImages4JustifiedGallery()
    {
        $iPage = $this->getInt('page', 1);
        $this->iCurrentSection = $this->getInt('sectionId');
        $sAlbumAlias = $this->getStr('albumalias', '');
        $this->photosLimit = Parameters::getValByName($this->iCurrentSection, 'content', 'photosLimit', true);
        $this->openAlbum = Parameters::getValByName($this->iCurrentSection, 'content', 'openAlbum', true);
        $bLastChunk = false;

        // Если нет ограничений на кол-во выводимых изоб-й, то они все выведутся при первичной инициализации шаблона "Строка"
        if (!$this->photosLimit) {
            exit;
        }

        // Список id альбомов
        $asAlbumIds = [];

        // Есть alias - значит находимся на детальной
        if ($sAlbumAlias) {
            if ($aAlbum = gallery\Album::getByAlias($sAlbumAlias, $this->iCurrentSection)) {
                $asAlbumIds = $aAlbum['id'];
            }
        } else {
            // Список альбомов в текущей странице
            $aAlbums = $this->getListAlbumsWithPreview();
            $asAlbumIds = ArrayHelper::getColumn($aAlbums, 'id');
        }

        $iTotalCount = 0;
        // Изображения, соответствующие альбомам
        $aPhotos = gallery\Photo::getListWithSeoData($asAlbumIds, $this->iCurrentSection, true, $this->photosLimit, $iPage, $iTotalCount);

        if ($this->photosLimit * ($iPage) >= $iTotalCount) {
            $bLastChunk = true;
        }

        $aHtmlImages = [];
        foreach ($aPhotos as $aPhoto) {
            $aHtmlImages[] = $this->renderTemplate('listImages.php', [
                'images' => [$aPhoto],
                'openAlbum' => $this->openAlbum,
            ]);
        }

        echo json_encode([
            'images' => $aHtmlImages,
            'bLastChunk' => $bLastChunk,
        ]);
        exit;
    }
}// class
