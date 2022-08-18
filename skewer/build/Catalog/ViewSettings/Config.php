<?php

use skewer\base\site\Layer;

$aConfig['name'] = 'ViewSettings';
$aConfig['title'] = 'Настройка вывода';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Модуль настройки вывода для каталога';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::CATALOG;
$aConfig['languageCategory'] = 'catalog';

return $aConfig;
