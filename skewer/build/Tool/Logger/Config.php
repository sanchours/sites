<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Logger';
$aConfig['title'] = 'Система логирования';
$aConfig['version'] = '1.000a';
$aConfig['description'] = 'Система логирования событий на сайте';
$aConfig['revision'] = '0001';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::SYSTEM;

return $aConfig;
