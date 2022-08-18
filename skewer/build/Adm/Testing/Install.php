<?php

namespace skewer\build\Adm\Testing;

use skewer\base\orm\Query;
use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    public function install()
    {
        if (USECLUSTERBUILD) {
            $this->fail('Нельзя установить модуль тестирования на кластерные площадки!');
        }

        $pathTest = WEBPATH . 'tests';

        if (!is_dir($pathTest)) {
            mkdir($pathTest);
        }

        if (!is_dir($pathTest . '/lastReport')) {
            mkdir($pathTest . '/lastReport');
        }

        $query = 'CREATE TABLE IF NOT EXISTS `manual_test` (
             `id` int(11) NOT NULL AUTO_INCREMENT,
             `path_id` varchar(255) NOT NULL,
             `check` BOOLEAN NOT NULL,
             PRIMARY KEY (`id`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

        Query::SQL($query);

        return true;
    }

    public function uninstall()
    {
        return true;
    }
}
