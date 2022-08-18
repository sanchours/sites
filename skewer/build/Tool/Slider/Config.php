<?php

use skewer\base\site\Layer;
use skewer\build\Tool\LeftList;

$aConfig['name'] = 'Slider';
$aConfig['title'] = 'Slider';
$aConfig['version'] = '1.000';
$aConfig['description'] = 'Управление слайдами на сайте';
$aConfig['revision'] = '0002';
$aConfig['layer'] = Layer::TOOL;
$aConfig['group'] = LeftList\Group::CONTENT;

return $aConfig;
