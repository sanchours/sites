<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Auth';
$aConfig['title'] = 'Регистрация';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Модуль Регистрации и авторизации';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'auth';

$aConfig['dependency'] = [
    ['Profile', Layer::PAGE],
    ['Auth', Layer::TOOL],
];

return $aConfig;
