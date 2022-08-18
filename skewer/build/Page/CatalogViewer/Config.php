<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'CatalogViewer';
$aConfig['title'] = 'Просмотр каталога';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Модуль вывода каталога';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'catalog';

$aConfig['dependency'] = [
    ['CatalogFilter', Layer::PAGE],

    ['Order', Layer::ADM],
    ['Catalog', Layer::ADM],

    ['CardEditor', Layer::CATALOG],
    ['Dictionary', Layer::CATALOG],
    ['Goods', Layer::CATALOG],
    ['LeftList', Layer::CATALOG],
];

/* Настраиваемые параметры модуля */
$aConfig['param_settings'] = 'skewer\build\Page\CatalogViewer\ParamSettings';

$aConfig['events'][] = [
    'event' => \skewer\components\GalleryOnPage\Api::EVENT_GET_GALLERY,
    'class' => \skewer\build\Page\CatalogViewer\Api::className(),
    'method' => 'registerGallery',
];

return $aConfig;
