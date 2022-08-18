<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'GuestBook';
$aConfig['title'] = 'Модуль отзывов';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Модуль отзывов';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'review';

$aConfig['dependency'] = [
    ['GuestBook', Layer::ADM],
    ['Review', Layer::TOOL],
];

/* Настраиваемые параметры модуля */
$aConfig['param_settings'] = 'skewer\build\Page\GuestBook\ParamSettings';

return $aConfig;
