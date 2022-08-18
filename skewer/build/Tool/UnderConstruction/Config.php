<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'UnderConstruction';
$aConfig['title'] = 'Заглушка для сайта';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Вывод заглушки для сайта (На реконструкции)';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;
$aConfig['languageCategory'] = 'uconst';

return $aConfig;
