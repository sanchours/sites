<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Articles';
$aConfig['version'] = '1.0';
$aConfig['title'] = 'Статьи';
$aConfig['description'] = 'Модуль статей';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;

/* Настраиваемые параметры модуля */
$aConfig['param_settings'] = 'skewer\build\Page\Articles\ParamSettings';

return $aConfig;
