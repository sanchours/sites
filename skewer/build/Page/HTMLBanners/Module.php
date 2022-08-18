<?php

namespace skewer\build\Page\HTMLBanners;

use skewer\base\section\Tree;
use skewer\base\site_module;
use skewer\build\Adm\HTMLBanners\models\Banners;

class Module extends site_module\page\ModulePrototype
{
    public $Location = 'left';
    private $template;

    /**
     * @var int ID главной страницы
     */
    private $defaultSection = 0;

    public function init()
    {
        $this->setParser(parserPHP);

        $this->defaultSection = \Yii::$app->sections->main();
        $this->template = 'banner_' . $this->Location . '.php';
    }

    public function execute()
    {
        \Yii::$app->router->setLastModifiedDate(Banners::getMaxLastModifyDate());

        $aParams = [];
        $aParams['location'] = $this->Location;
        $aParams['current_section'] = $this->sectionId();

        if ($this->sectionId() == $this->defaultSection) {
            $aBanners = Banners::getBannersOnMain($this->sectionId(), $this->Location);
        } else {
            $aParentSections = Tree::getSectionParents($this->sectionId());
            if ($aParentSections) {
                $aParams['parent_sections'] = implode(',', $aParentSections);
            } else {
                $aParams['parent_sections'] = $this->sectionId();
            }

            $aBanners = Banners::getBanners($this->sectionId(), $aParentSections, $this->Location);
        }

        if ($aBanners) {
            $this->setData('dataProvider', $aBanners);
        }

        $this->setTemplate($this->template);

        return psComplete;
    }

    /** {@inheritdoc} */
    public function canHaveContent()
    {
        return false;
    }
}//class
