<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Params';
$aConfig['title'] = 'Параметры';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Управление системными параметрами для раздела';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::ADM;

$aConfig['events'][] = [
    'event' => \skewer\base\section\models\TreeSection::EVENT_AFTER_DELETE,
    'eventClass' => \skewer\base\section\models\TreeSection::className(),
    'class' => \skewer\base\section\models\ParamsAr::className(),
    'method' => 'removeSection',
];

return $aConfig;
