<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Text';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Текст';
$aConfig['description'] = 'Модуль для вывода блока с текстом';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;

$aConfig['param_settings'] = 'skewer\build\Page\Text\ParamSettings';

return $aConfig;
