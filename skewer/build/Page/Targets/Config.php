<?php

/* main */
use skewer\base\site\Layer;

$aConfig['name'] = 'Targets';
$aConfig['title'] = 'Модуль целей';
$aConfig['version'] = '2.000a';
$aConfig['description'] = 'Модуль целей, уходящих в аналитику поисковиков';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::PAGE;

$aConfig['dependency'] = [];

return $aConfig;
