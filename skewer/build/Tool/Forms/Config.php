<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Forms';
$aConfig['title'] = 'Конструктор форм';
$aConfig['version'] = '2.000';
$aConfig['description'] = 'Конструктор форм';
$aConfig['revision'] = '0003';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::CONTENT;

return $aConfig;
