<?php

use skewer\base\site\Layer;

$aConfig['name'] = 'CatalogFilter';
$aConfig['title'] = 'Фильтр каталожных позиций';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Модуль для вывода фильтра каталожных позиций';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;
$aConfig['languageCategory'] = 'catalogFilter';

/* Настраиваемые параметры модуля */
$aConfig['param_settings'] = 'skewer\build\Page\CatalogFilter\ParamSettings';

return $aConfig;
