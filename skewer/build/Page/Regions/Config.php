<?php

use skewer\base\site\Layer;

$aConfig['name'] = 'Regions';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Регионы';
$aConfig['description'] = 'Модуль определения региона';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'regions';

$aConfig['dependency'] = [
    ['Regions', Layer::TOOL],
    ['Labels', Layer::TOOL],
];

return $aConfig;
