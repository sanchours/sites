<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

/* main */
$aConfig['name'] = 'SectionSettings';
$aConfig['title'] = 'Разделы';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Настройки для разделов';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::ADMIN;
$aConfig['languageCategory'] = 'page'; //?

return $aConfig;
