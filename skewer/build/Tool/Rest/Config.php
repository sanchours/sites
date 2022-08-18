<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Rest';
$aConfig['title'] = 'REST API';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Модуль для включения REST API';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::SYSTEM;

return $aConfig;
