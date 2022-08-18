<?php

namespace skewer\build\Page\News;

use skewer\base\site;
use skewer\base\site_module;
use skewer\base\SysVar;
use skewer\build\Adm\News as NewsAdm;
use skewer\build\Adm\News\models\News;
use skewer\build\Design\Zones;
use skewer\components\auth\CurrentAdmin;
use skewer\components\gallery\Album;
use skewer\components\GalleryOnPage\Api as GalOnPageApi;
use skewer\components\microdata\reviews\Api as MicroData;
use skewer\components\seo;
use skewer\components\traits\CanonicalOnPageTrait;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Публичный модуль вывода новостей
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype
{
    use CanonicalOnPageTrait;

    public $parentSections;
    public $onPage = 10;
    public $template = 'list.twig';
    public $template_detail = 'detail_page.twig';
    public $titleOnMain = 'Новости';
    public $showFuture;
    public $showOnMain;  // отменяет фильтр по разделам
    public $allNews;
    public $sortNews;
    public $showList;
    public $onMainShowType = 'list';
    public $lengthAnnounceOnMain = null;
    // public $usePageLine = 1;

    private static $showDetailLink = null;

    public $aParameterList = [
        'order' => 'DESC',
        'future' => '',
        'byDate' => '',
        'on_page' => '',
        'on_main' => '',
        'section' => '',
        'all_news' => '',
    ];

    public $section_all = 0;

    protected function onCreate()
    {
        if ($this->showList) {
            $this->setUseRouting(false);
        }
    }

    public function init()
    {
        $this->onPage = abs($this->onPage);

        $this->setParser(parserTwig);

        $this->aParameterList['on_page'] = $this->onPage;

        if ($this->allNews) {
            $this->aParameterList['all_news'] = 1;
        }
        if ($this->showOnMain) {
            $this->aParameterList['all_news'] = 1;
        }

        if ($this->parentSections) {
            $this->aParameterList['section'] = $this->parentSections;
        } else {
            $this->aParameterList['section'] = $this->sectionId();
        }

        if ($this->sectionId() == \Yii::$app->sections->main()) {
            $this->aParameterList['on_main'] = 1;
        }
        if ($this->showFuture) {
            $this->aParameterList['future'] = 1;
        }
        if ($this->sortNews) {
            $this->aParameterList['order'] = $this->sortNews;
        }

        return true;
    }

    // func

    /**
     * Выводит новость по псевдониму.
     *
     * @param $news_alias
     *
     * @throws NotFoundHttpException
     *
     * @return int
     */
    public function actionView($news_alias)
    {
        $news = News::getPublicNewsByAliasAndSec($news_alias, $this->sectionId());

        return $this->showOne($news);
    }

    /**
     * Выводит новость по id.
     *
     * @param $id
     *
     * @throws NotFoundHttpException
     *
     * @return int
     */
    public function actionViewById($id)
    {
        $news = News::getPublicNewsById($id);
        if (isset($news['parent_section'], $news['news_alias'])) {
            $this->setCanonicalByAlias(
                (int) $news['parent_section'],
                $news['news_alias']
            );
        }


        return $this->showOne($news);
    }

    /**
     * Выводит новость.
     *
     * @param null|News $news
     *
     * @throws NotFoundHttpException
     *
     * @return int
     */
    public function showOne($news)
    {
        $this->setStatePage(Zones\Api::DETAIL_LAYOUT);

        if (!$news) {
            throw new NotFoundHttpException();
        }
        if (!$this->canShowNews($news)) {
            throw new NotFoundHttpException();
        }

        $news = Api::parseOne($news, $this->sectionId());

        \Yii::$app->router->setLastModifiedDate($news->last_modified_date);

        //Метатеги для списка новостей
        $this->setSEO(new NewsAdm\Seo(0, $this->sectionId(), $news->getAttributes()));

        // меняем заголовок
        site\Page::setTitle($news->title);

        // добавляем элемент в pathline
        site\Page::setAddPathItem($news->title, \Yii::$app->router->rewriteURL($news->getUrl()));

        $this->setData('hideDate', $this->hasHideDatePublication());
        $this->setData('microData', MicroData::microData4News($news));

        if (Api::bShowGalleryInDetail()) {
            $this->setFotoramaData($news);
        }

        $this->setData('bShowGalleryInDetail', Api::bShowGalleryInDetail());
        $this->setData('news', $news);
        $this->setTemplate($this->template_detail);

        return psComplete;
    }

    /**
     * Выводит список новостей.
     *
     * @param int $page номер страницы
     * @param string $date фильтр по дате
     *
     * @return int
     */
    public function actionIndex($page = 1, $date = '')
    {
        if (!$this->onPage) {
            return psComplete;
        }

        $this->aParameterList['page'] = $page;

        if (!empty($date)) {
            $sDateFilter = date('Y-m-d', strtotime($date));
            $this->aParameterList['byDate'] = $sDateFilter;
        }

        $iAllSection = 0;
        if ($this->showOnMain) {
            $iAllSection = $this->section_all;
        }

        if ($iAllSection) {
            $this->aParameterList['all_news'] = 0;
            $this->aParameterList['section'] = $iAllSection;
            $this->setData('section_all', $iAllSection);
        }

        // Получаем список новостей
        $dataProvider = News::getPublicList($this->aParameterList);
        \Yii::$app->router->setLastModifiedDate(News::getMaxLastModifyDate());

        //формат для изображения
        if ($this->zoneType == 'left' || $this->zoneType == 'right') {
            $sFormatImage = 'column';
        } elseif (mb_strpos($this->template, 'list_on_main') !== false) {
            $sFormatImage = 'on_main';
        } else {
            $sFormatImage = 'list';
        }

        $aNews = Api::parseList($dataProvider->getModels(), $this->sectionId());

        //пагинатор
        $iPage = $dataProvider->getPagination()->page + 1;
        $iCount = $dataProvider->getTotalCount();
        $this->getPageLine($iPage, $iCount, $this->sectionId(), [], ['onPage' => $this->aParameterList['on_page']], 'aPages', !$this->isMainModule());
        $this->showPagination();

        $this->showCarousel();
        $this->setDefImage($sFormatImage);

        $this->setTemplate($this->template);
        $this->setData('aNews', $aNews);
        $this->setData('titleOnMain', $this->titleOnMain);
        $this->setData('asset_path', $this->getAssetWebDir());
        $this->setData('showDetailLink', self::hasShowDetailLink());
        $this->setData('hideDate', $this->hasHideDatePublication());
        $this->setData('onMainShowType', $this->onMainShowType);
        $this->setData('zone', $this->zoneType);
        $this->setData('bShowGallery', Api::bShowGalleryInList());
        $this->setData('sFormatImage', $sFormatImage);
        $this->setData('date', $date);
        $this->setData('lengthAnnounceOnMain', $this->lengthAnnounceOnMain);

        return psComplete;
    }

    /**
     * Устанавливает seo данные.
     *
     * @param seo\SeoPrototype $oSeo - объект seo класса. Записывается в переменные окружения и обрабатывается в модуле Page\SEOMetatags
     */
    public function setSEO(seo\SeoPrototype $oSeo)
    {
        $this->setEnvParam(seo\Api::SEO_COMPONENT, $oSeo);

        $this->setEnvParam(
            seo\Api::OPENGRAPH,
            site_module\Parser::parseTwig('OpenGraph.twig', OpenGraph::setOpenGraph(new News($oSeo->getDataEntity()), $oSeo), __DIR__ . \DIRECTORY_SEPARATOR . $this->getTplDirectory() . \DIRECTORY_SEPARATOR)
        );

        site\Page::reloadSEO();
    }

    /**
     * Флаг вывода ссылки "Подробнее".
     *
     * @return bool
     */
    public static function hasShowDetailLink()
    {
        if (self::$showDetailLink === null) {
            self::$showDetailLink = (bool) SysVar::get('News.showDetailLink');
        }

        return self::$showDetailLink;
    }

    /**
     * Скрывать дату публикации?
     *
     * @return bool
     */
    public function hasHideDatePublication()
    {
        return (bool) SysVar::get('News.hasHideDatePublication', false);
    }

    /**
     * Пагинатор на страницу.
     */
    public function showPagination()
    {
        //выводим пагинацию только в зоне "контент"
        if ($this->zoneType == 'content') {
            $this->setData('showPagination', 1);
        }
    }

    /**
     * Установить изображение по умолчанию.
     *
     * @param $sFormatImage
     */
    public function setDefImage($sFormatImage)
    {
        if ($idAlbum = SysVar::get('Gallery.DefaultImg')) {
            $sImg = Album::getFirstActiveImage($idAlbum, $sFormatImage);
            $this->setData('defImg', $sImg);
        }
    }

    /**
     * Показ карусели.
     */
    public function showCarousel()
    {
        //Если вывод на главной и установлен тип вывода: карусель
        if ($this->sectionId() == \Yii::$app->sections->main() && $this->onMainShowType == 'carousel') {
            $this->template = 'list_on_main_carousel.twig';
            $this->setData('gallerySettings_news', GalOnPageApi::getSettingsByEntity('News', true));
        }
    }

    /**
     * Устанавливает данные для фоторамы в шаблон.
     *
     * @param News $news - новость
     */
    protected function setFotoramaData(News $news)
    {
        // нет изображений
        if (!ArrayHelper::getValue($news->gallery, 'images')) {
            return;
        }

        list($iMaxWidth, $iMaxHeight) = Album::getDimensions4Fotorama($news->gallery['gallery_id'], 'big', $news->gallery['images'], true);
        $this->setData('iMaxWidth', $iMaxWidth);
        $this->setData('iMaxHeight', $iMaxHeight);
    }

    /**
     * Проверяем, нужно ли отдать 404ю вместо новости
     * Для админов показываются как активные, так и неактивные новости
     * @param News $news
     * @return bool
     */
    private function canShowNews(News $news): bool
    {
        $bIsAdmin = CurrentAdmin::isLoggedIn();
        return $news->active || $bIsAdmin;
    }
}
