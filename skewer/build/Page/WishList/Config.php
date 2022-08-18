<?php

use skewer\base\site\Layer;

$aConfig['name'] = 'WishList';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Отложенные товары';
$aConfig['description'] = 'Модуль отложенных товаров';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;

$aConfig['dependency'] = [
    ['Profile', Layer::TOOL],
];

return $aConfig;
