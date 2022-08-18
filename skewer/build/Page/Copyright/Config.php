<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Copyright';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Копирайт';
$aConfig['description'] = 'Модуль защиты от копирования контента';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;

$aConfig['dependency'] = [
    ['Copyright', Layer::TOOL],
];

return $aConfig;
