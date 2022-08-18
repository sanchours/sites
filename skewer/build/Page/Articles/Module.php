<?php

namespace skewer\build\Page\Articles;

use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\site;
use skewer\base\site_module\page\ModulePrototype;
use skewer\base\SysVar;
use skewer\build\Design\Zones;
use skewer\build\Page\Articles\Model\Articles;
use skewer\build\Page\Articles\Model\ArticlesRow;
use skewer\components\auth\CurrentAdmin;
use skewer\components\gallery\Album;
use skewer\components\microdata\reviews\Api as MicroData;
use skewer\components\seo;
use skewer\components\traits\CanonicalOnPageTrait;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Модуль системы статей
 * Class Module.
 */
class Module extends ModulePrototype
{
    use CanonicalOnPageTrait;

    public $parentSections;
    public $onPage = 10;
    public $template = '';
    public $template_detail = 'detail_page.twig';
    public $titleOnMain = 'Статьи';
    public $showFuture;
    public $showOnMain;  // отменяет фильтр по разделам
    public $allArticles;
    public $sortArticles;
    public $showList;
    // public $usePageLine = 1;

    public $aParameterList = [
        'order' => 'DESC',
        'future' => '',
        'byDate' => '',
        'on_page' => '',
        'on_main' => '',
        'section' => '',
        'active' => '',
        'all_articles' => '',
    ];

    public $section_all = 0;

    public function init()
    {
        $this->onPage = abs($this->onPage);

        $this->setParser(parserTwig);
        $this->aParameterList['on_page'] = $this->onPage;

        if ($this->allArticles) {
            $this->aParameterList['all_articles'] = 1;
        }
        if ($this->showOnMain) {
            $this->aParameterList['all_articles'] = 1;
        }

        if ($this->sectionId() == \Yii::$app->sections->main()) {
            $this->aParameterList['on_main'] = 1;
        }
        if ($this->parentSections) {
            $this->aParameterList['section'] = $this->parentSections;
        } else {
            $this->aParameterList['section'] = $this->sectionId();
        }

        if ($this->showFuture) {
            $this->aParameterList['future'] = 1;
        }
        if ($this->sortArticles) {
            $this->aParameterList['order'] = $this->sortArticles;
        }

        return true;
    }

    // func

    protected function onCreate()
    {
        if ($this->showList) {
            $this->setUseRouting(false);
        }
    }

    public function execute()
    {
        if (!$this->onPage) {
            return psComplete;
        }

        $iPage = $this->getInt('page', 1);
        $iArticlesId = $this->getInt('articles_id', 0);
        $sArticlesAlias = $this->getStr('articles_alias', '');
        $sDateFilter = $this->getStr('date');

        if (!empty($sDateFilter)) {
            $sDateFilter = date('Y-m-d', strtotime($sDateFilter));
            $this->aParameterList['byDate'] = $sDateFilter;
        }

        /*
         * Если в запросе передается ID или её алиас, значит необходимо вывести конкретную статью;
         * если нет - выводим список, разбитый по страницам.
         */
        if (($iArticlesId || $sArticlesAlias) && !$this->showList) {
            $this->setStatePage(Zones\Api::DETAIL_LAYOUT);

            /* @var Model\ArticlesRow $oArticlesRow */
            if ($sArticlesAlias) {
                $oArticlesRow = Model\Articles::getPublicByAliasAndSec($sArticlesAlias, $this->sectionId());
            } else {
                $oArticlesRow = Model\Articles::getPublicById($iArticlesId);
                if ($oArticlesRow instanceof ArticlesRow) {
                    $this->setCanonicalByAlias(
                        $oArticlesRow->parent_section,
                        $oArticlesRow->articles_alias
                    );
                }

            }

            if (!$oArticlesRow) {
                throw new NotFoundHttpException();
            }
            if (!$this->canShowArticle($oArticlesRow)) {
                throw new NotFoundHttpException();
            }

            $oArticlesRow = Api::parseOne($oArticlesRow, $this->sectionId());

            \Yii::$app->router->setLastModifiedDate($oArticlesRow->last_modified_date);

            $this->setSEO(new \skewer\build\Adm\Articles\Seo(0, $this->sectionId(), $oArticlesRow->getData()));

            // меняем заголовок
            site\Page::setTitle($oArticlesRow->title);

            // добавляем элемент в pathline
            site\Page::setAddPathItem($oArticlesRow->title, \Yii::$app->router->rewriteURL($oArticlesRow->getUrl()));

            $this->setData('hideDate', $this->hasHideDatePublication());
            $this->setData('microData', MicroData::microData4Articles($oArticlesRow));

            if (Api::bShowGalleryInDetail()) {
                $this->setFotoramaData($oArticlesRow);
            }

            $this->setData('bShowGalleryInDetail', Api::bShowGalleryInDetail());
            $this->setData('oArticlesRow', $oArticlesRow);
            $this->setTemplate($this->template_detail);
        } else {
            $iAllSection = 0;
            if ($this->showOnMain) {
                $iAllSection = $this->section_all;
            }

            if ($iAllSection) {
                $this->aParameterList['all_articles'] = 0;
                $this->aParameterList['section'] = $iAllSection;
            }

            // Получаем список новостей
            $iCount = 0;
            $aArticlesList = Model\Articles::getPublicList($this->showList ? 1 : $iPage, $this->aParameterList, $iCount);
            $aArticlesList = Api::parseList($aArticlesList, $this->sectionId());

            $this->setData('bShowGallery', Api::bShowGalleryInList());

            \Yii::$app->router->setLastModifiedDate(Articles::getMaxLastModifyDate());

            if ($iCount) {
                $this->setData('aArticlesList', $aArticlesList);

                if ($iAllSection) {
                    $this->setData('section_all', $iAllSection);
                }

                $aURLParams = [];

                if (!empty($sDateFilter)) {
                    $aURLParams['date'] = $sDateFilter;
                }

                $this->getPageLine(
                    $iPage,
                    $iCount,
                    $this->sectionId(),
                    $aURLParams,
                    ['onPage' => $this->aParameterList['on_page']],
                    'aPages',
                    !$this->isMainModule()
                );
            }

            $this->setData('hideDate', $this->hasHideDatePublication());

            $sTmp = Parameters::getValByName($this->sectionId(), '.', 'template', true);
            $sTmpArticles = Tree::getSectionByAlias('articles', \Yii::$app->sections->templates());

            if ($sTmp != $sTmpArticles || $this->zoneType != 'content') {
                $this->setData('titleOnMain', $this->titleOnMain);
            }

            $this->setData('showPagination', $this->hasShowPagination());

            if (!$this->template) {
                $this->template = Api::getTemplate4TypeShow($this->sectionId(), $this->zoneType);
            }

            $this->setData('zone', $this->zoneType);
            $this->setTemplate($this->template);
        }

        site\Page::reloadSEO();

        return psComplete;
    }

    // func

    /**
     * Установка данных для фоторамы.
     *
     * @param ArticlesRow $oArticlesRow
     */
    protected function setFotoramaData(ArticlesRow $oArticlesRow)
    {
        // нет изображений
        if (!ArrayHelper::getValue($oArticlesRow->gallery, 'images')) {
            return;
        }

        list($iMaxWidth, $iMaxHeight) = Album::getDimensions4Fotorama($oArticlesRow->gallery['gallery_id'], 'big', $oArticlesRow->gallery['images'], true);
        $this->setData('iMaxWidth', $iMaxWidth);
        $this->setData('iMaxHeight', $iMaxHeight);
    }

    public function setSEO(seo\SeoPrototype $oSeo)
    {
        $this->setEnvParam(seo\Api::SEO_COMPONENT, $oSeo);
        // OPENGRAPH статьи пока не поддерживают
        $this->setEnvParam(seo\Api::OPENGRAPH, '');
        site\Page::reloadSEO();
    }

    /**
     * Скрывать дату публикации ?
     *
     * @return bool
     */
    public function hasHideDatePublication()
    {
        return (bool) SysVar::get('Articles.hasHideDatePublication', false);
    }

    /**
     * Выводить пагинатор?
     * Пагинатор выводится только на внутренних страницах типа статьи в зоне контент
     *
     * @return bool
     */
    private function hasShowPagination()
    {
        //выводим пагинацию только в зоне "контент"
        $bRes = false;

        $sCurrentTemplate = Parameters::getValByName($this->sectionId(), '.', 'template', true);
        $sTemplateArticles = Tree::getSectionByAlias('articles', \Yii::$app->sections->templates());

        if (($this->zoneType == 'content')
            && ($this->sectionId() != \Yii::$app->sections->main())
            && ($sCurrentTemplate == $sTemplateArticles)) {
            $bRes = true;
        }

        return $bRes;
    }

    /**
     * Проверяем, нужно ли отдать 404ю вместо статьи
     * Для админов показываются как активные, так и неактивные статьи
     * @param ArticlesRow $article
     * @return bool
     */
    private function canShowArticle(ArticlesRow $article): bool
    {
        $bIsAdmin = CurrentAdmin::isLoggedIn();
        return $article->active || $bIsAdmin;
    }
}// class
