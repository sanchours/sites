<?php

namespace skewer\build\Tool\LeftList;

/* main */
use skewer\base\site\Layer;
use skewer\components\search;

$aConfig['name'] = 'LeftList';
$aConfig['title'] = 'Список';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Список модулей Tool слоя для вывода в панели навинации';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::TOOL;
$aConfig['isSystem'] = true;
$aConfig['languageCategory'] = 'adm';

$aConfig['events'][] = [
    'event' => search\Api::EVENT_CMS_SEARCH,
    'class' => Api::className(),
    'method' => 'search',
];

return $aConfig;
