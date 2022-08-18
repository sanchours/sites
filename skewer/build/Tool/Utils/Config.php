<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/* main */
$aConfig['name'] = 'Utils';
$aConfig['title'] = 'Утилиты';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Инструменты для работы с сайтом';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::SYSTEM;

return $aConfig;
