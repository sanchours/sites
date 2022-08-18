<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Dictionary';
$aConfig['title'] = 'Справочники';
$aConfig['version'] = '1.000b';
$aConfig['description'] = 'Общесайтовые справочники';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::CONTENT;
$aConfig['languageCategory'] = 'dict';

return $aConfig;
