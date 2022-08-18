<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'News';
$aConfig['version'] = '2.0';
$aConfig['title'] = 'Новости';
$aConfig['description'] = 'Модуль новостной системы';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;

$aConfig['dependency'][] = ['PathLine', Layer::PAGE];
$aConfig['dependency'][] = ['Title', Layer::PAGE];
$aConfig['dependency'][] = ['SEOMetatags', Layer::PAGE];

/* Настраиваемые параметры модуля */
$aConfig['param_settings'] = 'skewer\build\Page\News\ParamSettings';

$aConfig['events'][] = [
    'event' => \skewer\components\GalleryOnPage\Api::EVENT_GET_GALLERY,
    'class' => \skewer\build\Page\News\Api::className(),
    'method' => 'registerGallery',
];

return $aConfig;
