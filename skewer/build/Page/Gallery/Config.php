<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Gallery';
$aConfig['title'] = 'Галерея';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Модуль вывода фотогаллерей';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;

/* Настраиваемые параметры модуля */
$aConfig['param_settings'] = 'skewer\build\Page\Gallery\ParamSettings';

return $aConfig;
