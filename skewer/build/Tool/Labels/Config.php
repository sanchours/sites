<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList\Group;

/* main */
$aConfig['name'] = 'Labels';
$aConfig['title'] = 'Метки';
$aConfig['version'] = '1.000';
$aConfig['description'] = '';
$aConfig['revision'] = '0001';
$aConfig['useNamespace'] = true;
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = Group::CONTENT;

return $aConfig;
