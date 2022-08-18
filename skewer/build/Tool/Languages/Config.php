<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Languages';
$aConfig['title'] = 'Управление языками';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Управление языками и языковыми метками';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::LANGUAGE;

return $aConfig;
