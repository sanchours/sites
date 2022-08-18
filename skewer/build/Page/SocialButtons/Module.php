<?php

namespace skewer\build\Page\SocialButtons;

use skewer\base\site;
use skewer\base\site_module;

class Module extends site_module\page\ModulePrototype
{
    public $soclinkContent = 0;
    public $soclinkNews = 0;
    public $soclinkArticles = 0;
    public $soclinkGoods = 0;
    public $soclinkGallery = 0;

    /** @var string шаблон плашки соц кнопок */
    public $template = 'block.twig';

    /**
     * Первичная инициализация.
     */
    public function init()
    {
    }

    /**
     * Выполнение модуля.
     *
     * @return int
     */
    public function execute()
    {
        $bShow = false;

        $contentModule = site\Page::getMainModuleProcess();
        if ($contentModule) {
            if (!$contentModule->isComplete()) {
                return psWait;
            }

            switch ($contentModule->getModuleClass()) {
                case 'skewer\build\Page\News\Module':
                    $newsAlias = -1;
                    if ($contentModule->oRouter->getStr('news_alias', $newsAlias) ||
                        $contentModule->oRouter->getStr('news_id', $newsAlias)) {
                        if ($this->soclinkNews) {
                            $bShow = true;
                        }
                    }

                break;
                case 'skewer\build\Page\Articles\Module':
                    $articleAlias = -1;
                    if ($contentModule->oRouter->getStr('articles_alias', $articleAlias) ||
                        $contentModule->oRouter->getStr('id', $articleAlias)) {
                        if ($this->soclinkArticles) {
                            $bShow = true;
                        }
                    }

                    break;

                case 'skewer\build\Page\Gallery\Module':

                    $bShow = $this->getEnvParam('gallery_photos') && $this->soclinkGallery;

                break;

                case 'skewer\build\Page\CatalogViewer\Module':
                    $catalogAlias = -1;

                    if ($contentModule->oRouter->getStr('goods-alias', $catalogAlias) ||
                        $contentModule->oRouter->getStr('goods_id', $catalogAlias)) {
                        if ($this->soclinkGoods) {
                            $bShow = true;
                        }
                    }
                    break;
            }
        } elseif ($this->soclinkContent) {
            $bShow = true;
        }

        $this->setTemplate($this->template);

        $this->setData('show', $bShow);

        return psComplete;
    }

    // func

    /** {@inheritdoc} */
    public function canHaveContent()
    {
        return false;
    }
}
