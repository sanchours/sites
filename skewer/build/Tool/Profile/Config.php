<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Profile';
$aConfig['title'] = 'Личный кабинет';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Модуль настройки личного кабинета в клиентской части';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ORDER;

$aConfig['dependency'] = [
    ['WishList', Layer::PAGE],
];

return $aConfig;
