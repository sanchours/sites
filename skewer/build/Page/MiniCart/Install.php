<?php

namespace skewer\build\Page\MiniCart;

use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    public function install()
    {
        $iNewPageSection = \Yii::$app->sections->tplNew();

        $this->addParameter($iNewPageSection, 'object', 'MiniCart', '', 'minicart', '');
        $this->addParameter($iNewPageSection, 'layout', 'left,right', '', 'minicart', '');
        $this->addParameter($iNewPageSection, 'template', 'cart.twig', '', 'minicart', '');

        $this->addParameter($iNewPageSection, 'object', 'MiniCart', '', 'minicartHead', '');
        $this->addParameter($iNewPageSection, 'layout', 'head', '', 'minicartHead', '');
        $this->addParameter($iNewPageSection, 'template', 'head.twig', '', 'minicartHead', '');

        return true;
    }

    public function uninstall()
    {
        $iNewPageSection = \Yii::$app->sections->tplNew();

        $this->removeParameter($iNewPageSection, 'object', 'minicart');
        $this->removeParameter($iNewPageSection, 'layout', 'minicart');
        $this->removeParameter($iNewPageSection, 'template', 'minicart');

        $this->removeParameter($iNewPageSection, 'object', 'minicartHead');
        $this->removeParameter($iNewPageSection, 'layout', 'minicartHead');
        $this->removeParameter($iNewPageSection, 'template', 'minicartHead');

        return true;
    }
}
