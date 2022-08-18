<?php

namespace skewer\build\Page\CatalogFilter;

use skewer\base\section\params\Type;
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
        \Yii::$app->db->createCommand(
            'CREATE TABLE IF NOT EXISTS `filterSettings4Card` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `title` varchar(255) NOT NULL,
                  `card_id` int(11) NOT NULL,
                  `alt_title` varchar(255) NOT NULL,
                  `meta_title` varchar(255) NOT NULL,
                  `meta_description` text NOT NULL,
                  `meta_keywords` varchar(255) NOT NULL,
                  `staticContent1` text NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;'
        )->execute();

        $this->setParameter(\Yii::$app->sections->tplNew(), '.title', 'CatalogFilter', 'Фильтр для каталога', '', '', 0);
        $this->setParameter(\Yii::$app->sections->tplNew(), 'layout', 'CatalogFilter', 'left', '', '', 0);
        $this->setParameter(\Yii::$app->sections->tplNew(), 'object', 'CatalogFilter', 'CatalogFilter', '', '', 0);
        $this->setParameter(\Yii::$app->sections->tplNew(), 'title', 'CatalogFilter', '', '', 'catalogFilter.BlockTitle', Type::paramString);
        $this->setParameter(\Yii::$app->sections->main(), 'title', 'CatalogFilter', '', '', 'catalogFilter.BlockTitle', Type::paramString);

        return true;
    }

    // func

    public function uninstall()
    {
        return true;
    }

    // func
}// class
