<?php

namespace skewer\build\Page\RecentlyViewed;

use skewer\base\section\models\ParamsAr;
use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\base\SysVar;
use skewer\build\Page\CatalogViewer;
use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        $iNewPageSection = \Yii::$app->sections->tplNew();

        $sGroupContent = 'RecentlyViewed';
        $this->addParameter($iNewPageSection, 'object', 'RecentlyViewed', '', $sGroupContent);
        $this->addParameter($iNewPageSection, 'layout', 'content', '', $sGroupContent);
        $this->addParameter($iNewPageSection, 'iOnPage', 5, '', $sGroupContent);
        $this->addParameter($iNewPageSection, 'sTpl', 'gallery', '', $sGroupContent);

        $sGroupLeft = 'RecentlyViewedLeft';
        $this->addParameter($iNewPageSection, 'object', 'RecentlyViewed', '', $sGroupLeft);
        $this->addParameter($iNewPageSection, 'layout', 'left', '', $sGroupLeft);
        $this->addParameter($iNewPageSection, 'iOnPage', 5, '', $sGroupLeft);
        $this->addParameter($iNewPageSection, 'sTpl', 'gallery', '', $sGroupLeft);
        $this->addParameter($iNewPageSection, 'template', 'RecentlyViewed_in_column.twig', '', $sGroupLeft);

        $sGroupRight = 'RecentlyViewedRight';
        $this->addParameter($iNewPageSection, 'object', 'RecentlyViewed', '', $sGroupRight);
        $this->addParameter($iNewPageSection, 'layout', 'right', '', $sGroupRight);
        $this->addParameter($iNewPageSection, 'iOnPage', 5, '', $sGroupRight);
        $this->addParameter($iNewPageSection, 'sTpl', 'gallery', '', $sGroupRight);
        $this->addParameter($iNewPageSection, 'template', 'RecentlyViewed_in_column.twig', '', $sGroupRight);

        $iCatalogTplId = $this->getCatalogTplId();

        // Добавление параметров в каталог
        $this->addParameter($iCatalogTplId, 'recentlyViewedOnPage', 5, '', 'content');
        $this->addParameter($iCatalogTplId, 'recentlyViewedTpl', 'gallery', '', 'content');

        return true;
    }

    // func

    public function uninstall()
    {
        $this->deleteModuleParams();
        SysVar::del('catalog.goods_recentlyViewed');

        unset($_SESSION[Module::RECENTLY_VIEWED]);

        // Удаление параметров из каталога
        $iCatalogTplId = $this->getCatalogTplId();
        $aCatalogSections = Template::getSubSectionsByTemplate($iCatalogTplId);
        $aCatalogSections[] = $iCatalogTplId;
        ParamsAr::deleteAll(['group' => 'content', 'name' => ['recentlyViewedTpl', 'recentlyViewedOnPage'], 'parent' => $aCatalogSections]);

        return true;
    }

    // func

    /**
     * Получить id шаблона каталога.
     *
     * @return int | false
     */
    private function getCatalogTplId()
    {
        $aTemplatesId = Tree::getSubSections(\Yii::$app->sections->templates(), true, true);

        $aParam = Parameters::getList($aTemplatesId)
            ->group('content')
            ->name(Parameters::object)
            ->value(CatalogViewer\Module::getNameModule())
            ->asArray()
            ->get();

        return (isset($aParam[0]['parent'])) ? (int) $aParam[0]['parent'] : false;
    }
}// class
