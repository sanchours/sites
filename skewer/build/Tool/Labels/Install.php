<?php

namespace skewer\build\Tool\Labels;

use skewer\base\orm\Query;
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
        $query = 'CREATE TABLE IF NOT EXISTS `labels` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `title` varchar(250) NOT NULL,
                    `alias` varchar(250) NOT NULL,
                    `default` text NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

        Query::SQL($query);

        return true;
    }

    // func

    public function uninstall()
    {
        return true;
    }

    // func
}//class
