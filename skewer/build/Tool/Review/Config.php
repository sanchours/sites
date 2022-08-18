<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Review';
$aConfig['title'] = 'Отзывы';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Модуль управления отзывами';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::CONTENT;
$aConfig['languageCategory'] = 'review';

$aConfig['dependency'] = [
    ['GuestBook', Layer::PAGE],
    ['GuestBook', Layer::ADM],
];

$aConfig['events'][] = [
    'event' => \skewer\components\GalleryOnPage\Api::EVENT_GET_GALLERY,
    'class' => \skewer\build\Tool\Review\Api::className(),
    'method' => 'registerGallery',
];

return $aConfig;
