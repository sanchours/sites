<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Documents';
$aConfig['title'] = 'Модуль Документов';
$aConfig['version'] = '1.000b';
$aConfig['description'] = 'Модуль Документов';
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
