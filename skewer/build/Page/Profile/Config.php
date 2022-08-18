<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Profile';
$aConfig['title'] = 'Личный кабинет';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Модуль ЛК';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'auth';

$aConfig['dependency'] = [
    ['Auth', Layer::PAGE],
    ['Auth', Layer::TOOL],
];

return $aConfig;
