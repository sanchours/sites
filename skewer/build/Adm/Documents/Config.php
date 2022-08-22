<?php

/* main */
use skewer\base\section\models\TreeSection;
use skewer\base\site\Layer;
use skewer\build\Adm\Documents\models\Documents;

$aConfig['name'] = 'Documents';
$aConfig['title'] = 'Документы';
$aConfig['version'] = '1.1';
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
    'class' => Documents::className(),
    'method' => 'removeSection',
];

$aConfig['events'][] = [
    'event' => skewer\components\modifications\Api::EVENT_GET_MODIFICATION,
    'class' => \skewer\build\Adm\Documents\Api::className(),
    'method' => 'getLastMod',
];

return $aConfig;
