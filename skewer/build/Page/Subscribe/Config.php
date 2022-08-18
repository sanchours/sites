<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Subscribe';
$aConfig['title'] = 'Рассылка';
$aConfig['version'] = '2.0';
$aConfig['description'] = 'Модуль рассылки';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;

$aConfig['dependency'] = [
    ['Subscribe', Layer::TOOL],
];

return $aConfig;
