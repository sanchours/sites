<?php

use skewer\base\site\Layer;

$aConfig['name'] = 'CategoryViewer';
$aConfig['title'] = 'Разводка';
$aConfig['version'] = '1.0';
$aConfig['description'] = 'Модуль вывода разводки категорий';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::PAGE;

$aConfig['param_group'] = 'CategoryViewer'; // Группа параметров
$aConfig['languageCategory'] = 'CategoryViewer';

return $aConfig;
