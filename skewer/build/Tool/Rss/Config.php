<?php

/* main */
use skewer\base\section\models\TreeSection;
use skewer\base\site\Layer;
use skewer\build\Tool\Rss;

$aConfig['name'] = 'Rss';
$aConfig['version'] = '2.0';
$aConfig['title'] = 'Модуль RSS';
$aConfig['description'] = 'Модуль настройки RSS';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = \skewer\build\Tool\LeftList\Group::CONTENT;

$aConfig['events'][] = [
    'event' => TreeSection::EVENT_AFTER_UPDATE,
    'eventClass' => TreeSection::className(),
    'class' => Rss\Api::className(),
    'method' => 'updateSection',
];

$aConfig['events'][] = [
    'event' => TreeSection::EVENT_BEFORE_DELETE,
    'eventClass' => TreeSection::className(),
    'class' => Rss\Api::className(),
    'method' => 'removeSection',
];

$aConfig['events'][] = [
    'event' => Rss\Api::EVENT_REBUILD_RSS,
    'class' => Rss\Api::className(),
    'method' => 'rebuildRss',
];

return $aConfig;
