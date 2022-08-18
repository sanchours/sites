<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/* main */
$aConfig['name'] = 'Regions';
$aConfig['title'] = 'Регионы';
$aConfig['version'] = '1.000';
$aConfig['description'] = '';
$aConfig['revision'] = '0001';
$aConfig['useNamespace'] = true;
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::CONTENT;

$aConfig['dependency'] = [
    ['Regions', Layer::PAGE],
    ['Labels', Layer::TOOL],
];

return $aConfig;
