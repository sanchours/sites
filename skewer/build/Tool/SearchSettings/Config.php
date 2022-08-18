<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/* main */
$aConfig['name'] = 'SearchSettings';
$aConfig['title'] = 'Поиск';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Настройки поиска';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;
$aConfig['languageCategory'] = 'search';

return $aConfig;
