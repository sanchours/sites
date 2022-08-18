<?php

/* main */
use skewer\base\section\models\TreeSection;
use skewer\base\site\Layer;
use skewer\build\Adm\GuestBook\models\GuestBook;

$aConfig['name'] = 'GuestBook';
$aConfig['title'] = 'Отзывы';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Система администрирования Отзывов';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::ADM;
$aConfig['languageCategory'] = 'review';

$aConfig['dependency'] = [
    ['GuestBook', Layer::PAGE],
    ['Review', Layer::TOOL],
];

$aConfig['events'][] = [
    'event' => TreeSection::EVENT_BEFORE_DELETE,
    'eventClass' => TreeSection::className(),
    'class' => GuestBook::className(),
    'method' => 'removeSection',
];

$aConfig['events'][] = [
    'event' => skewer\components\modifications\Api::EVENT_GET_MODIFICATION,
    'class' => \skewer\build\Adm\GuestBook\Api::className(),
    'method' => 'getLastMod',
];

return $aConfig;
