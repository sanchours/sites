<?php

namespace skewer\build\Page\WishList;

use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\base\SysVar;
use skewer\components\config\InstallPrototype;
use Yii;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        $this->executeSQLQuery('CREATE TABLE IF NOT EXISTS `wish_list`
                            ( `id` int(11) NOT NULL AUTO_INCREMENT,
                              `id_goods` varchar(255) NOT NULL,
                              `id_users` int(12) NOT NULL,
                              PRIMARY KEY (`id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

        $this->executeSQLQuery('CREATE TABLE IF NOT EXISTS `wish_list_message` 
                            ( `id` INT(12) NOT NULL AUTO_INCREMENT,
                              `id_users` INT(12) NOT NULL,
                              `text` VARCHAR(250) NOT NULL,
                              PRIMARY KEY (`id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

        SysVar::set('WishList.Enable', false);
        SysVar::set('WishList.OnPage', 10);
        SysVar::set('WishList.ShowMod', 'List');

        $idProfile = Tree::getSectionByAlias('profile', \Yii::$app->sections->tools());

        Parameters::setParams(Yii::$app->sections->tplNew(), 'WishList', 'object', 'WishList');
        Parameters::setParams(Yii::$app->sections->tplNew(), 'WishList', 'idSectionWishList', $idProfile);
        Parameters::setParams(Yii::$app->sections->tplNew(), 'WishList', 'layout', 'head');

        $sShowVal = Parameters::getShowValByName(Yii::$app->sections->tplNew(), '.layout', 'head', true);
        Parameters::setParams(Yii::$app->sections->tplNew(), '.layout', 'head', '{show_val}', "{$sShowVal},WishList", 'editor.site_header');
        $sTplCatalog = Template::getCatalogTemplate();
        $sShowVal = Parameters::getShowValByName($sTplCatalog, '.layout', 'head', true);
        Parameters::setParams($sTplCatalog, '.layout', 'head', '{show_val}', "{$sShowVal},WishList", 'editor.site_header');
    }

    // func

    public function uninstall()
    {
        Parameters::removeByGroup('WishList', Yii::$app->sections->tplNew());
        $sShowVal = Parameters::getShowValByName(Yii::$app->sections->tplNew(), '.layout', 'head', true);
        $sShowVal = str_replace(',WishList', '', $sShowVal);
        Parameters::setParams(Yii::$app->sections->tplNew(), '.layout', 'head', '{show_val}', "{$sShowVal}", 'editor.site_header');
        $sTplCatalog = Template::getCatalogTemplate();
        $sShowVal2 = Parameters::getShowValByName($sTplCatalog, '.layout', 'head', true);
        $sShowVal2 = str_replace(',WishList', '', $sShowVal2);
        Parameters::setParams($sTplCatalog, '.layout', 'head', '{show_val}', "{$sShowVal2}", 'editor.site_header');
        SysVar::del('WishList.Enable');
        SysVar::del('WishList.OnPage');
        SysVar::del('WishList.ShowMod');

        return true;
    }

    // func
}//class
