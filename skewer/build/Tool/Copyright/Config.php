<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Copyright';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Копирайт';
$aConfig['description'] = 'Настройка модуля защиты от копирования контента';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = \skewer\build\Tool\LeftList\Group::CONTENT;
$aConfig['languageCategory'] = 'copyright';

$aConfig['dependency'] = [
    ['Copyright', Layer::PAGE],
];

return $aConfig;
