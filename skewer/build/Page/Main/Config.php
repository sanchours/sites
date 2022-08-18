<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Main';
$aConfig['title'] = 'Страница сайта';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Модуль сборки типовой страницы сайта по параметрам раздела';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'page';

$aConfig['events'][] = [
    'event' => \skewer\components\GalleryOnPage\Api::EVENT_GET_GALLERY,
    'class' => \skewer\build\Page\Main\Api::className(),
    'method' => 'registerGallery',
];

return $aConfig;
