<?php

use skewer\base\site\Layer;

$aConfig = [];
$aConfig['name'] = 'Filters';
$aConfig['title'] = 'Настройки фильтров';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Модуль настройки фильтров';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::CATALOG;
$aConfig['languageCategory'] = 'filters';

return $aConfig;
