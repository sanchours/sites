<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'CatalogMaps';
$aConfig['title'] = 'Просмотр карты';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Модуль вывода карты в раздел';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'сatalogMaps';
$aConfig['dependency'] = [
    ['Maps', Layer::TOOL],
    ['EditorMap', Layer::CMS],
];

return $aConfig;
