<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/*
 * User: kolesnikiv
 * Date: 10.05.12
 * Time: 14:32
 */
$aConfig['name'] = 'Backup';
$aConfig['title'] = 'Резервные копии';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Модулья для выполнения резервного копирования из админки';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;

return $aConfig;
