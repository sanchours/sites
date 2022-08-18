<?php

namespace skewer\build\Tool\Regions;

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
        $query = "CREATE TABLE IF NOT EXISTS `regions` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `domain` varchar(100) NOT NULL,
                    `utm` varchar(100) NOT NULL,
                    `city` varchar(100) NOT NULL,
                    `region` varchar(100) NOT NULL,
                    `fed_district` varchar(100) NOT NULL,
                    `default` int(1) DEFAULT '0',
                    `active` int(1) DEFAULT '1',
                    
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";

        Query::SQL($query);

        $query = 'CREATE TABLE IF NOT EXISTS `region_labels` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `region_id` int(11) NOT NULL,
                    `label_id` int(11) NOT NULL,
                    `value` text NOT NULL,
                    PRIMARY KEY (`id`),
                    
                    FOREIGN KEY (region_id)
                    REFERENCES regions(id)
                    ON DELETE CASCADE,
                    
                    FOREIGN KEY (label_id)
                    REFERENCES labels(id)
                    ON DELETE CASCADE 
                    
                    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ';

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
