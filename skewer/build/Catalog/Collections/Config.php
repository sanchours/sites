<?php

namespace skewer\build\Catalog\Collections;

use skewer\base\site\Layer;

$aConfig['name'] = 'Collections';
$aConfig['title'] = 'Коллекции';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Управление коллекциями каталога';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::CATALOG;

$aConfig['dependency'] = [
    ['Collections', Layer::ADM],
];

$aConfig['events'][] = [
    'event' => \skewer\components\search\Api::EVENT_GET_ENGINE,
    'class' => Search::className(),
    'method' => 'getSearchEngine',
];

return $aConfig;
