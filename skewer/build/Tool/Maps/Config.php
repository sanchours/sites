<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Maps';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Карты';
$aConfig['description'] = 'Настройки отображения и работы карт';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = \skewer\build\Tool\LeftList\Group::CONTENT;
$aConfig['languageCategory'] = 'Maps';

$aConfig['dependency'] = [
    ['CatalogMaps', Layer::PAGE],
];

return $aConfig;
