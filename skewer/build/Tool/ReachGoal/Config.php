<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/* main */
$aConfig['name'] = 'ReachGoal';
$aConfig['title'] = 'ReachGoal';
$aConfig['version'] = '2.000';
$aConfig['description'] = 'Модуль настройки целей';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::SEO;

$aConfig['events'][] = [
    'event' => 'target_delete',
    'class' => skewer\build\Tool\ReachGoal\Api::className(),
    'method' => 'checkTarget',
];

return $aConfig;
